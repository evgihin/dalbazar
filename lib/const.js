  
  module.exports.params = {
  userId: ":userId/",
  itemId: ":itemId/"
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
//  host: "http://localhost:3000/uploads/",
  host: "http://localhost:3000",
  //local: "../public/uploads/",
  local: "/tmp",
  avatarAdd: "/users/me/avatar/"
  }
