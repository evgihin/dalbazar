const mongoose = require('mongoose')

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
  avatar: String,
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
  avatar: String,
views: {
type: Number,
default: 0
},
items: [{type: mongoose.Schema.Types.ObjectId, ref: 'Item'}],
},
{ timestamps: true }
);

module.exports.userModel = mongoose.model('User', userSchema)
module.exports.itemModel = mongoose.model('Item', itemSchema)
