/*Biblioteki */
const express = require('express');
const mongoose = require('mongoose');
const multer = require('multer')
const path = require('path')
const cookieSession = require('cookie-session')
const app = express();
const {userModel, itemModel, imageModel} = require('./lib/models')
const {paths, params} = require('./lib/const')
const {filePath} = require('./lib/func')
const fs = require('fs')

/*Configuracii*/
app.use(express.urlencoded({ extended: true }), express.json());
app.set('view engine', 'ejs')
mongoose.connect('mongodb://127.0.0.1:27017/mestodb');
app.listen(3000);
//app.use(express.static(paths.local))
app.use('/tmp/', express.static('/tmp/'))


const storage = multer.diskStorage({
destination: function (req, file, cb) {
const {userId, itemId} = req.session
if(file.fieldname == 'avatar')
var folderName = '/userId_' + userId 
else
var folderName ='/userId_' + userId + '/itemId_' + itemId 
if(!fs.existsSync(paths.local + folderName))
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
app.post(paths.userAdd,  async function (req, res) {
const {username, password, name, about} = req.body
const user = userModel({username: username, password: password, name: name, about: about})
await user.save()
req.session.userId = user._id.toString()
req.session.user = user
res.redirect(paths.avatarAdd)
})

app.get(paths.avatarAdd, function (req, res) {res.render('addAvatar')})
app.post(paths.avatarAdd, upload.single('avatar'), async function (req, res) {
const {avatar} = req.body
const user = await userModel.findById(req.session.userId)
//user.avatar = filePath(req.file)
user.avatar = req.file.path
await user.save()
res.end()
/*
console.log('USER')
console.log(user)
*/
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

/*
async function itemList(req, res) {
const {search} = req.body
if(search)
var doc = await itemModel.find({name: {$regex: search}})
else
var doc = await itemModel.find({})
res.render('itemList', {doc: doc, route: req.route.path})
  }
  */
async function itemList(req, res) {
const {search} = req.body
if(search)
var doc = await itemModel.find({name: {$regex: search}}).populate({path: 'images'})
else
//var doc = await itemModel.find({})
var doc = await itemModel.find({}).populate({path: 'images'})
res.render('itemList', {doc: doc, route: req.route.path})
console.log('DOC')
console.log(doc)
  }

app.get(paths.items, itemList)
app.post(paths.items, itemList)

app.get(paths.users + params.userId, userPage)
app.post(paths.users + params.userId, userPage)

app.get(paths.items + params.itemId, async function (req, res) {
const doc = await itemModel.findById(req.params.itemId) 
res.render('itemPage', {doc: doc})
})


/*
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
*/
app.get(paths.profile, function (req, res){res.render('updateForm', {doc: req.session.user})})
app.post(paths.profile, upload.single('avatar'), async function (req, res) {
const {username, password, name, about, avatar} = req.body
const user = await userModel.findById(req.session.userId)
user.username = username
user.password = password
user.name = name
user.about = about
if(req.file)
//user.avatar = filePath(req.file)
user.avatar = req.file.path
await user.save()
req.session.user = user
res.redirect(paths.profile)
console.log('USER')
console.log(user)
console.log('FILE')
console.log(req.file)
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


app.get(paths.itemAdd, function (req, res){res.render('addItemForm', {route: req.route.path})})
app.post(paths.itemAdd, async function (req, res) {
const user = await userModel.findById(req.session.userId)
const {name, about} = req.body
const item = itemModel({name: name, about: about, ownerId: user._id, ownerName: user.username})

await item.save()

user.items.push(item._id)
await user.save()
req.session.itemId = item._id.toString()
res.redirect(paths.imageAdd)
})

/*
app.get(paths.imageAdd, function (req, res){res.render('addImageForm', {route: req.route.path})})
app.post(paths.imageAdd, upload.array('images', 2), async function (req, res) {
const item = await itemModel.findById(req.session.itemId)
const {images} = req.body
req.files.forEach(async function(img){ 
item.images.push(filePath(img, req.session.userId, req.session.itemId))
})
await item.save()
//res.end()
res.redirect(paths.userItems + req.session.itemId)
//res.redirect(paths.userItems + req.session.itemId)
console.log('IMAGE')
console.log(item.images[0])

})
*/
/*
app.get(paths.imageAdd, function (req, res){res.render('addImageForm', {route: req.route.path})})
app.post(paths.imageAdd, upload.array('images', 2), async function (req, res) {
const item = await itemModel.findById(req.session.itemId)
const {images} = req.body
const links = []
req.files.forEach(async function(img){ 
links.push(filePath(img))
})
const image = await imageModel.insertMany(req.files)
await item.save()
links.forEach(function(link) {
console.log('{' + 'link: ' +  link + '}' + ',')
})
res.end()
console.log('IMAGES')
console.log(item.images)
console.log('LINK')
console.log(links)
console.log('FILES')
console.log(req.files)
console.log(image)
})
*/
app.get(paths.imageAdd, function (req, res){res.render('addImageForm', {route: req.route.path})})
app.post(paths.imageAdd, upload.array('images', 2), async function (req, res) {
const item = await itemModel.findById(req.session.itemId)
const {images} = req.body
/*
const links = []
req.files.forEach(async function(img){ 
links.push(filePath(img))
})
*/
const image = await imageModel.insertMany(req.files, {lean: true})
image.forEach(function(image) {
item.images.push(image._id)
})
await item.save()
/*
links.forEach(function(link) {
console.log('{' + 'link: ' +  link + '}' + ',')
})
*/
const doc = await itemModel.findById(req.session.itemId).populate({path: 'images'})
res.end()
console.log('IMAGES')
console.log(item.images)
/*
console.log('LINK')
console.log(links)
console.log('FILES')
console.log(req.files)
*/
console.log('IMAGE')
console.log(image)
console.log('ITEM')
console.log(doc.images[0].path)
})


/*
async function userItems(req, res) {
const {search} = req.body
const {userId} = req.session
if(search)
var doc = await userModel.findById(userId).populate({path: 'items', match:{name: {$regex: search}}}).exec()
else
var doc = await userModel.findById(userId).populate('items').exec()
console.log('ITEMS')
console.log(doc.items)
res.render('userItems', {doc: doc, route: req.route.path})
}
app.get(paths.userItems, userItems)
app.post(paths.userItems, userItems)
*/
async function userItems(req, res) {
const {search} = req.body
const {userId} = req.session
if(search)
var doc = await userModel.findById(userId).populate({path: 'items', match:{name: {$regex: search}}}).exec()
else
//var doc = await userModel.findById(userId).populate({path: 'items', populate: {path: 'images'}}).exec()
var doc = await userModel.findById(userId)
await doc.populate({path:'items', populate: {path: 'images'}})
console.log('ITEMS')
//console.log(doc.items[0].images[0].path)
doc.items.forEach(function (item) {
console.log(item.images[0].path)
})
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
/*
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
*/
app.post(paths.userItems + params.itemId, upload.array('images', 2), async function (req, res) {
const {name, about, images, ownerId, ownerName} = req.body
const item = await itemModel.findById(req.params.itemId)
item.name = name 
item.about = about
req.files.forEach(async function(img){ 
//item.images.push(filePath(img, req.session.userId, req.session.itemId))
item.images.push(filePath(img))
})
/*
req.files.forEach(async function(img){ 
const imagePath = filePath(img)
console.log(img)
item.images.push(imagePath)
})
*/
item.ownerId = ownerId
item.ownerName = ownerName
await item.save()
req.session.item = item

res.redirect(paths.userItems + req.params.itemId)
/*
console.log('PARAMS:' + req.session.itemId)
console.log(req.params.itemId)
console.log(req.session.itemId)
*/
console.log('FILES')
console.log(req.files)
console.log('IMAGES')
console.log(item.images)
console.log(item.images)
})
