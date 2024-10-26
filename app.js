/*Biblioteki */
const express = require('express');
const mongoose = require('mongoose');
const multer = require('multer')
const path = require('path')
const cookieSession = require('cookie-session')
const app = express();
const {userModel, itemModel, imageModel} = require('./lib/models')
//const {userModel, itemModel} = require('./lib/models')
const {paths, params} = require('./lib/const')
const {filePath} = require('./lib/func')
const fs = require('fs')

/*Configuracii*/
app.use(express.urlencoded({ extended: true }), express.json());
app.set('view engine', 'ejs')
mongoose.connect('mongodb://127.0.0.1:27017/mestodb');
app.listen(3000);
app.use(express.static('../public'))

const storage = multer.diskStorage({
destination: function (req, file, cb) {
const folderName ='userId_' + req.session.userId + '/' + 'itemId_' + req.session.itemId + '/'
//const folderName ='userId_' + req.session.userId + '/' 
if(!fs.existsSync(paths.local + folderName))
//fs.mkdirSync(paths.local + folderName)
fs.mkdirSync(paths.local + folderName, {recursive: true})
cb(null, paths.local + folderName)
},
filename: function (req, file, cb) {
const uniqueSuffix = Date.now()
cb(null, 'imageTimestamp_' + Date.now() + path.extname(file.originalname))
}
})

const upload = multer({storage: storage})

app.use(cookieSession({secret: '123'}))

/* locals */
app.locals.dateFormat =  function (date) {
if(date)
return date.toLocaleDateString()
}
  app.locals.paths = paths

/* routeri */
app.get(paths.userAdd, function (req, res) {res.render('addForm')})
app.post(paths.userAdd, upload.single('avatar'), async function (req, res) {
const {username, password, name, about, avatar} = req.body
const user = userModel({username: username, password: password, name: name, about: about, avatar: filePath(req.file)})
await user.save()
req.session.user = user
req.session.userId = user._id.toString()
  console.log(req.session.userId)
  console.log(name)
res.redirect(paths.items)
})


async function userPage(req, res) {
const {search} = req.body
const {userId} = req.params
if(search)
var doc = await userModel.findById(userId).populate({path: 'items', match:{name: {$regex: search}}}).exec()
else
var doc = await userModel.findById(userId).populate('items').exec()

res.render('userPage', {doc: doc, route: paths.users + userId})
console.log(doc)
}

async function itemList(req, res) {
const {search} = req.body
if(search)
var doc = await itemModel.find({name: {$regex: search}})
else
var doc = await itemModel.find({})
res.render('itemList', {doc: doc, route: req.route.path})
  }

app.get(paths.items, itemList)
app.post(paths.items, itemList)

app.get(paths.users + params.userId, userPage)
app.post(paths.users + params.userId, userPage)

app.get(paths.items + params.itemId, async function (req, res) {
const doc = await itemModel.findById(req.params.itemId) 
res.render('itemPage', {doc: doc})
})


app.get(paths.profile, function (req, res){res.render('updateForm', {doc: req.session.user})})
app.post(paths.profile, upload.single('avatar'), async function (req, res) {
const {username, password, name, about, avatar} = req.body
const user = await userModel.findById(req.session.userId)
user.username = username
user.password = password
user.name = name
user.about = about
if(req.file)
user.avatar = filePath(req.file)
await user.save()
req.session.user = user
res.redirect(paths.profile)
console.log(user)
})

app.get(paths.login, function (req, res){res.render('loginForm')})
app.post(paths.login, async  function (req, res) {
const {username, password} = req.body
const user = await userModel.findOne({username: username, password: password})
if(user){
req.session.user = user
req.session.userId = user._id.toString()
res.redirect(paths.items)
}
else
res.redirect(paths.login)
  console.log(user)
  })

app.get(paths.logout, function (req, res) {req.session = null; res.redirect(paths.items)})

/*
app.get(paths.itemAdd, function (req, res){res.render('addItemForm', {route: req.route.path})})
app.post(paths.itemAdd, upload.array('images', 2), async function (req, res) {
const user = await userModel.findById(req.session.userId)
const {name, about, images} = req.body
const item = itemModel({name: name, about: about, ownerId: user._id, ownerName: user.username})

//req.session.itemId = item._id.toString()

req.files.forEach(async function(img){ 
item.images.push(filePath(img))
})

await item.save()

user.items.push(item._id)
await user.save()
req.session.itemId = item._id.toString()
res.redirect(paths.userItems)
console.log('ITEM:' + item)
})
*/
app.get(paths.itemAdd, function (req, res){res.render('addItemForm', {route: req.route.path})})
app.post(paths.itemAdd, async function (req, res) {
const user = await userModel.findById(req.session.userId)
//const {name, about, images} = req.body
const {name, about} = req.body
const item = itemModel({name: name, about: about, ownerId: user._id, ownerName: user.username})

//req.session.itemId = item._id.toString()

/*
req.files.forEach(async function(img){ 
item.images.push(filePath(img))
})
*/

await item.save()

user.items.push(item._id)
await user.save()
req.session.itemId = item._id.toString()
res.redirect(paths.imageAdd)
console.log('ITEM:' + item)
console.log('ROUTE:' + req.route.path)
console.log('NAME:' + req.body.name)
console.log('ITEMID:' + req.session.itemId)
})

app.get(paths.imageAdd, function (req, res){res.render('addImageForm', {route: req.route.path})})
app.post(paths.imageAdd, upload.array('images', 2), async function (req, res) {
//app.post(paths.imageAdd, async function (req, res) {
const item = await itemModel.findById(req.session.itemId)
const {images} = req.body
//const {name, about} = req.body
//const item = itemModel({name: name, about: about, ownerId: user._id, ownerName: user.username})

//req.session.itemId = item._id.toString()

req.files.forEach(async function(img){ 
item.images.push(filePath(img))
})

await item.save()

/*
user.items.push(item._id)
await user.save()
req.session.itemId = item._id.toString()
*/
res.redirect(paths.userItems)
/*
console.log('ITEM:' + item)
console.log('ITEMID:' + req.session.itemId)
*/
})


async function userItems(req, res) {
const {search} = req.body
const {userId} = req.session
if(search)
var doc = await userModel.findById(userId).populate({path: 'items', match:{name: {$regex: search}}}).exec()
else
var doc = await userModel.findById(userId).populate('items').exec()
//var doc = await itemModel.findById(req.session.itemId).populate('images').exec()
//var doc = await userModel.findById(userId).populate('items').populate('images').exec()
//var doc = await userModel.findById(userId)

//console.log(doc)
console.log(doc.items)
console.log('ROUTE:' + req.route.path)
res.render('userItems', {doc: doc, route: req.route.path})
}
app.get(paths.userItems, userItems)
app.post(paths.userItems, userItems)


app.get(paths.userItems + params.itemId, async function (req, res){
const item = await itemModel.findById(req.params.itemId)
req.session.item = item
req.session.itemId = item._id.toString()
res.render('itemForm', {doc: item, route: paths.userItems + req.params.itemId })
})
app.post(paths.userItems + params.itemId, upload.array('images', 2), async function (req, res) {
const {name, about, images, ownerId, ownerName} = req.body
const item = await itemModel.findById(req.params.itemId)
item.name = name 
item.about = about
req.files.forEach(async function(img){ 
const imagePath = filePath(img)
console.log(img)
item.images.push(imagePath)
})
item.ownerId = ownerId
item.ownerName = ownerName
await item.save()
req.session.item = item

res.redirect(paths.userItems + req.params.itemId)
console.log('PARAMS:' + req.session.itemId)
console.log(req.params.itemId)
console.log(req.session.itemId)
console.log(item.images)
})
