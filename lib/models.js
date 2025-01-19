const mongoose = require('mongoose')
const {paths} = require('./const')

const imageSchema = new mongoose.Schema({
path: {
type: String,
default: paths.host +  '/dalbazar/media/img/item.jpg' 
}

});

const itemSchema = new mongoose.Schema({
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
images: [{type: mongoose.Schema.Types.ObjectId, ref: 'Image'}],
views: {
type: Number,
default: 0
},
//ownerId:  mongoose.Schema.Types.ObjectId,
ownerId:  String,
ownerName: String,
price: {
type: Number,
default: 0
},
tel: {
type: Number
},
email: {
type: String
},
tag: {
type: String
}
},
{ timestamps: true }
);

const userSchema = new mongoose.Schema({
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
  avatar:{
  type: String,
//  default: paths.host + '/img/avatar.png' 
  default: paths.host +  '/dalbazar/media/img/avatar.png' 
  },

items: [{type: mongoose.Schema.Types.ObjectId, ref: 'Item'}],
tel: {
type: Number
},
email: {
type: String
}
},
{ timestamps: true }
);

module.exports.imageModel = mongoose.model('Image', imageSchema)
module.exports.itemModel = mongoose.model('Item', itemSchema)
module.exports.userModel = mongoose.model('User', userSchema)
