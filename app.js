/*Biblioteki */
const express = require('express');
const mongoose = require('mongoose');
const multer = require('multer')
const path = require('path')
const cookieSession = require('cookie-session')
const app = express();
const upload = multer({dest:'./public/uploads'})
const {userModel, itemModel} = require('./lib/models')

/*Configuracii*/
app.use(express.urlencoded({ extended: true }), express.json());
app.set('view engine', 'ejs')
mongoose.connect('mongodb://127.0.0.1:27017/mestodb');
app.listen(3000);
app.use(express.static('public'))
app.use(cookieSession({secret: '123'}))

/* funkcii */
app.locals.viewsCounter = async function (userId) {
var user = await userModel.findById(userId)
user.views += 1
user = await user.save()
console.log('USERVIEWS: ' + user.views)
console.log('USERID: ' + userId)
userId = null
}
app.locals.dateFormat =  function (date) {
if(date)
return date.toLocaleDateString()
}
function avatarPath(file) {
if(file)
return 'http://localhost:3000/uploads/' + path.basename(file.path)
else
return 'http://localhost:3000/img/avatar.png'
}
function itemPath(file) {
if(file)
return 'http://localhost:3000/uploads/' + path.basename(file.path)
else
return 'http://localhost:3000/img/item.jpg'
}
function routePath(data) { return '/users/list/' + data + '/' }

async function userPage(req, res) {
if(req.body.search)
var doc = await userModel.findById(req.params.userId).populate({path: 'items', match:{name: {$regex: req.body.search}}}).exec()
else
var doc = await userModel.findById(req.params.userId).populate('items').exec()
res.render('userPage', {doc: doc, route: routePath(req.params.userId)})
console.log(doc)
}
async function all(req, res) {
if(req.body.search)
var doc = await userModel.find({name: {$regex: req.body.search}})
else
var doc = await userModel.find({})
console.log(doc)
res.render('list', {doc: doc})
  }

/* routeri */
app.get('/users/add', function (req, res) {res.render('addForm')})
app.post('/users/add', upload.single('avatar'), async function (req, res) {
req.session.user = await userModel({username: req.body.username, password: req.body.password, name: req.body.name, about: req.body.about, avatar: avatarPath(req.file)}).save()
  req.session.userId = req.session.user._id.toString()
res.redirect('/users/me')
})

app.get('/users/list', all)
app.post('/users/list/', all)

app.get('/users/list/:userId', userPage)
app.post('/users/list/:userId', userPage)

app.get('/users/list/:userId/:itemId', async function (req, res) {const doc = await itemModel.findById(req.params.itemId); res.render('itemPage', {doc: doc})})

app.get('/users/me', async function (req, res){res.render('updateForm', {doc: req.session.user})})
app.post('/users/me', upload.single('avatar'), async function (req, res) {
const update = {username: req.body.username, password: req.body.password, name: req.body.name, about: req.body.about, avatar: avatarPath(req.file)}
const options = { runValidators: false, returnDocument: 'after' }
req.session.user = await userModel.findByIdAndUpdate(req.session.userId, update, options )
res.redirect('/users/me')
})

app.get('/users/login', function (req, res){res.render('loginForm')})
app.post('/users/login', async  function (req, res) {
req.session.user = await userModel.findOne({username: req.body.username})
  req.session.userId = req.session.user._id.toString()
res.redirect('/users/me')
  })
app.get('/users/logout', function (req, res) {req.session = null; res.end()})

app.get('/users/me/addItem', function (req, res){res.render('addItemForm')})
app.post('/users/me/addItem', upload.single('avatar'), async function (req, res) {
const user = await userModel.findById(req.session.userId)
req.session.item = await itemModel({ name: req.body.name, about: req.body.about, avatar: itemPath(req.file)}).save()
req.session.itemId= req.session.item._id.toString()
user.items.push(req.session.item._id)
await user.save()
res.redirect('/users/me/item')
})

app.get('/users/me/item', async function (req, res){res.render('itemForm', {doc: req.session.item})})
app.post('/users/me/item', upload.single('avatar'), async function (req, res) {
const update = {name: req.body.name, about: req.body.about, avatar: itemPath(req.file)}
const options = { runValidators: false, returnDocument: 'after' }
req.session.item = await itemModel.findByIdAndUpdate(req.session.itemId, update, options )
  req.session.itemId= req.session.item._id.toString()
res.redirect('/users/me/item')
})
