/*Biblioteki */
const express = require('express');
const cookieParser = require('cookie-parser')
const mongoose = require('mongoose');
const multer = require('multer')
const path = require('path')
const cookieSession = require('cookie-session')

const app = express();
const upload = multer({dest:'./public/uploads'})

const {userModel, itemModel} = require('./lib/models')



/*Configuracii*/
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.set('view engine', 'ejs')
mongoose.connect('mongodb://127.0.0.1:27017/mestodb');
app.listen(3000);
app.use(express.static('public'))
app.use(cookieParser())
app.use(cookieSession({
secret: '123'
}))

/* Schemii */
/*
const ItemSchema = new mongoose.Schema({
  name: {
    type: String,
    minLength: 2,
    maxLength: 30,
  },
  about: {
    type: String,
    minLength: 2,
    maxLength: 30,
  },
  avatar: String,
views: {
type: Number,
default: 0
}
},
);

const UserSchema = new mongoose.Schema({
username: {
type: String,
required: true
},
password: {
type: String,
required: true
},
  name: {
    type: String,
    minLength: 2,
    maxLength: 30,
  },
  about: {
    type: String,
    minLength: 2,
    maxLength: 30,
  },
  avatar: String,
views: {
type: Number,
default: 0
},
items: [ItemSchema],
},
{ validateBeforeSave: false, timestamps: true }
);
*/
/* Modelii */
/*
const UserModel = mongoose.model('User', UserSchema);
const ItemModel = mongoose.model('Item', ItemSchema);
*/

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
async function addForm(req, res, next){
res.render('addForm')
next()
}
async function addItemForm(req, res, next){
res.render('addItemForm')
next()
}

async function loginForm(req, res, next){
res.render('loginForm')
next()
}


async function updateForm(req, res, next){
const doc = await userModel.findById(req.session.userId)
res.render('updateForm', {doc: doc})
next()
}

async function addItemForm(req, res, next){
const doc = await userModel.findById(req.session.userId)
res.render('addItemForm', {doc: doc})
next()
}
/*
async function allItemsForm(req, res, next) {
const route = "/users/list/" + req.params.userId + "/items"
console.log(route)
res.render('allItemsForm', {route: route})
next()
  }
  */
/*
async function listForm(req, res, next){
res.render('listForm')
next()
}
*/
async function all(req, res) {
if(req.body.search)
var doc = await userModel.find({name: {$regex: req.body.search}})
else
var doc = await userModel.find({})


console.log(doc)
res.render('list', {doc: doc})
  }
/*
async function allItems(req, res) {
const user = await userModel.findById(req.params.userId)
if(req.body.search)
var doc = await userModel.findById(req.params.userId).populate({path: 'items', match:{name: {$regex: req.body.search}}}).exec()
else
var doc = await userModel.findById(req.params.userId).populate('items').exec()
console.log(doc.items)
//res.render('allItems', {doc: doc.items})
res.render('userPage', {doc: doc})
//res.end()
  }
  */


async  function login(req, res) {
const user = await userModel.findOne({username: req.body.username})
  req.session.user = user
  req.session.userId = req.session.user._id.toString()
  console.log(req.session.userId)
  res.end()
  }

async  function logout(req, res) {
  req.session = null
  res.end()
  }

async function add(req, res) {
const filePath = 'http://localhost:3000/uploads/' + path.basename(req.file.path) 
const user = await userModel({username: req.body.username, password: req.body.password, name: req.body.name, about: req.body.about, avatar: filePath}).save()
console.log(user.username)
console.log(user.password)
console.log(user.name)
console.log(user.about)
console.log(user.avatar)
console.log(filePath)
res.end()
}
async function addItem(req, res) {
const filePath = 'http://localhost:3000/uploads/' + path.basename(req.file.path) 
const user = await userModel.findById(req.session.userId)
const item = await itemModel({ name: req.body.name, about: req.body.about, avatar: filePath}).save()
user.items.push(item._id)
await user.save()
//const user = await userModel.findById(req.session.userId)
//user.items.push({ name: req.body.name, about: req.body.about, avatar: filePath })
//const subdoc = user.items[0]
//console.log(subdoc)
//const items = await itemModel.find({})
//console.log(user)
//console.log(items)
//await user.save()
res.end()
}


async function userPage(req, res) {
const route = "/users/list/" + req.params.userId + '/'
//const doc = await userModel.findById(req.params.userId)
if(req.body.search)
var doc = await userModel.findById(req.params.userId).populate({path: 'items', match:{name: {$regex: req.body.search}}}).exec()
else
var doc = await userModel.findById(req.params.userId).populate('items').exec()
console.log(doc)
//res.render('byId', {doc: doc})
res.render('userPage', {doc: doc, route: route})
console.log(doc)
}

async function itemPage(req, res) {
//const doc = await userModel.findById(req.params.userId)
const doc = await userModel.findById(req.params.userId).populate({path:'items',match: {_id: req.params.itemId}})
//console.log(doc.items.pull('name'))
console.log(doc.items[0].name)
//res.render('byId', {doc: doc})
res.render('itemPage', {doc: doc.items[0]})
}

async function update(req, res) {
const filePath = 'http://localhost:3000/uploads/' + path.basename(req.file.path) 
const update = {username: req.body.username, password: req.body.password, name: req.body.name, about: req.body.about, avatar: filePath}
const options = { runValidators: false, returnDocument: 'after' }
const doc = await userModel.findByIdAndUpdate(req.session.userId, update, options )
console.log(doc)
res.end()
}



/* routeri */
app.get('/users/add', addForm)
app.post('/users/add', upload.single('avatar'), add)


//app.get('/users/list/', listForm)
app.get('/users/list/', all)
app.post('/users/list/', all)

app.get('/users/list/:userId', userPage)
app.get('/users/list/:userId/:itemId', itemPage)
app.post('/users/list/:userId', userPage)

/*
app.get('/users/list/:userId/items', allItemsForm)
app.post('/users/list/:userId/items', allItems)
*/

app.get('/users/me', updateForm)
app.post('/users/me', upload.single('avatar'), update)

app.get('/users/login', loginForm)
app.post('/users/login', login)

app.get('/users/logout', logout)

/*
app.get('/', function locals(req, res) {
console.log(app.locals.title)
res.render('locals')
})
*/
/*
app.get('/users/addItem', addItemForm)
app.post('/users/addItem', upload.single('avatar'), addItem)
*/
