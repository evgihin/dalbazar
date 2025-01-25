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

//mongoose.connect('mongodb://127.0.0.2:27017/mestodb');
mongoose.connect('mongodb://127.0.0.2:27017/dalbazar');
app.listen(paths.port);
app.use(paths.local, express.static(paths.local))


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
//const {username, password, name, about} = req.body
const {username, password, tel, email} = req.body
//const user = userModel({username: username, password: password, name: name, about: about})
const user = userModel({username: username, password: password, tel: tel, email: email})
await user.save()
req.session.userId = user._id.toString()
req.session.user = user
res.redirect(paths.avatarAdd)
console.log('USER')
console.log(user)
})

app.get(paths.avatarAdd, function (req, res) {res.render('addAvatar')})
app.post(paths.avatarAdd, upload.single('avatar'), async function (req, res) {
const {avatar} = req.body
const user = await userModel.findById(req.session.userId)
if(req.file)
user.avatar = req.file.path
//else
//user.avatar = avatar
//await user.save()
res.end()
console.log('AVATAR')
console.log(avatar)

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
var doc = await itemModel.find({name: {$regex: search}}).populate({path: 'images'})
else
var doc = await itemModel.find({}).populate({path: 'images'})
res.render('itemList', {doc: doc, route: req.route.path})
//res.redirect(paths.items)
console.log('DOC')
console.log(doc)
  }
/*
async function itemList(req, res) {
const {search} = req.body
var doc = await itemModel.find({tag: req.params.tag}).populate({path: 'images'})
res.render('itemList', {doc: doc, route: req.route.path})
console.log('DOC')
console.log(doc)
  }
  */


//app.get(paths.items , itemList)
//app.post(paths.items, itemList)
//app.all(paths.items, itemList)
app.all(paths.items, itemList)
app.all(paths.items + params.tag, itemList)

app.get(paths.users + params.userId, userPage)
app.post(paths.users + params.userId, userPage)

app.get(paths.items + params.itemId, async function (req, res) {
const {itemId} = req.params
//app.get('/items/list/:itemId', async function (req, res) {
const doc = await itemModel.findById(itemId).populate({path: 'images'}) 
console.log(doc)
res.render('itemPage', {doc: doc})
//console.log(itemId)
//console.log(doc.ownerId)
//console.log(doc.ownerName)
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
//const {name, about} = req.body
const {name, about, price, tel, email, tag} = req.body
//const item = itemModel({name: name, about: about, ownerId: user._id, ownerName: user.username, price: price, tel: tel, email: email})

const item = itemModel({name: name, about: about, ownerId: req.session.userId, ownerName: user.username, price: price, tel: tel, email: email, tag: tag})
await item.save()

user.items.push(item._id)
await user.save()
req.session.itemId = item._id.toString()
res.redirect(paths.imageAdd)
console.log(item)
})



app.get(paths.imageAdd, function (req, res){res.render('addImageForm', {route: req.route.path})})
app.post(paths.imageAdd, upload.array('images', 2), async function (req, res) {
const item = await itemModel.findById(req.session.itemId)
const {images} = req.body

//const image = await imageModel.insertMany(req.files)
const image = imageModel({})
await image.save()
if(req.files) {
const image = await imageModel.insertMany(req.files)

//await item.save()

image.forEach(function(image) {
item.images.push(image._id)
})
}
item.images.push(image._id)
await item.save()

const doc = await itemModel.findById(req.session.itemId).populate({path: 'images'})
res.end()
console.log('IMAGES')
console.log(item.images)

console.log('IMAGE')
console.log(image)
console.log('ITEM')
console.log('IMAGES')
console.log(doc.images)
})



async function userItems(req, res) {
const {search} = req.body
const {userId} = req.session
const doc = await userModel.findById(userId)
if(search)
await doc.populate({path:'items', populate: {path: 'images'}, match:{name: {$regex: search}}})
else
await doc.populate({path:'items', populate: {path: 'images'}})
console.log('ITEMS')
res.render('userItems', {doc: doc, route: req.route.path})
console.log('IMAGES')
console.log(doc.items)
}
app.get(paths.userItems, userItems)
app.post(paths.userItems, userItems)


app.get(paths.userItems + params.itemId, async function (req, res){
const item = await itemModel.findById(req.params.itemId)
await item.populate({path: 'images'})
req.session.item = item
res.render('itemForm', {doc: item, route: paths.userItems + req.params.itemId })
})

app.post(paths.userItems + params.itemId, upload.array('images', 2), async function (req, res) {
//const {name, about, images} = req.body
const {name, about, images, price, tel, email} = req.body
const item = await itemModel.findById(req.params.itemId)
item.name = name 
item.about = about
item.price = price
item.tel = tel
item.email = email
const image = await imageModel.insertMany(req.files)

image.forEach(function(image) {
item.images.push(image._id)
})


item.ownerId = req.session.userId
item.ownerName = req.session.user.name
await item.save()
req.session.item = item

res.redirect(paths.userItems + req.session.itemId)

})
app.get(paths.imageRemove + params.imageId, async function (req, res) {
const doc = await imageModel.deleteOne({_id: req.params.imageId})
res.redirect(paths.userItems + req.session.itemId)
})
app.get(paths.itemRemove + params.itemId, async function (req, res) {
const doc = await itemModel.deleteOne({_id: req.params.itemId})
res.redirect(paths.userItems)
console.log(doc)
})
/*
app.get(paths.home + params.tag, async function(req, res){

var doc = await itemModel.find({tag: req.params.tag}).populate({path: 'images'})
res.render('home', {doc: doc, route: req.route.path})
})
*/
app.all(paths.home, async function(req, res, next){

var doc = await itemModel.find({}).populate({path: 'images'})
res.render('home', {doc: doc, route: req.route.path})
next()
},itemList)

/*
app.all(paths.items + params.tag , async function(req, res){

//var doc = await itemModel.find({tag: req.params.tag}).populate({path: 'images'})
console.log(req.params.tag)
//res.render('itemList', {doc: doc, route: req.route.path})
})
*/
