
const {paths} =  require('./const')
const path = require('path')



module.exports.filePath = function (file) {
if(file){
return  paths.host + file.path
}
}
