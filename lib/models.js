const mongoose = require('mongoose')
const {paths} = require('./const')

const imageSchema = new mongoose.Schema({
path: {
type: String
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
  /*
  images: {
  type: [String],
  },
  */
//images: new mongoose.Schema({path: String}),
images: [{type: mongoose.Schema.Types.ObjectId, ref: 'Image'}],
//images: [imageSchema],
views: {
type: Number,
default: 0
},
ownerId:  mongoose.Schema.Types.ObjectId,
ownerName: String
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
  default: paths.host + '/img/avatar.png' 
  },

items: [{type: mongoose.Schema.Types.ObjectId, ref: 'Item'}],
},
{ timestamps: true }
);

module.exports.imageModel = mongoose.model('Image', imageSchema)
module.exports.itemModel = mongoose.model('Item', itemSchema)
module.exports.userModel = mongoose.model('User', userSchema)
