  
  module.exports.params = {
  userId: ":userId/",
  itemId: ":itemId/",
  imageId: ":imageId/",
  tag: ":tag/"
  }

  module.exports.paths = {
  userAdd: "/users/add/",
  profile: "/users/me/",
  items:  "/items/list/",
  users: "/users/list/",
  login: "/users/login/",
  logout: "/users/logout/",
  itemAdd: "/users/me/item/add/",
  imageAdd: "/users/me/item/add/images/add",
  userItem: "/users/me/item/",
  userItems: "/users/me/items/",
  host: "http://localhost:3000",
  //local: "/tmp",
local: "/dalbazar/media",
  avatarAdd: "/users/me/avatar/",
  imageRemove: "/users/me/item/remove/images/remove/",
  port: "3000",
  itemRemove: "/users/me/items/remove/",
  home: "/home"
  }
