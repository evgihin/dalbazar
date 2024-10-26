
const {paths} =  require('./const')
const path = require('path')

module.exports.filePath = function (file) {
if(file){
//return paths.host + '/uploads/' + path.basename(file.path)
//return paths.host + '/uploads/' + file.filename
return paths.host + file.destination + file.filename
//return paths.host + '/uploads/' + path.basename(file.path) + path.extname(file.originalname)
}
}
