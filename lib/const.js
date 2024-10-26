  
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
  itemAdd: "/users/me/addItem/",
  imageAdd: "/users/me/addItem/addImage",
  userItem: "/users/me/item/",
  userItems: "/users/me/items/",
  host: "http://localhost:3000",
  local: "../public/uploads/"
  }
