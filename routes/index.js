const express = require('express');
const router  = express.Router();

/* login page */
/* render login.ejs page with GET request to root directory */
router.get('/', (req,res)=>{
    res.render('welcome');
})

/* register page */
/* render register.ejs page with GET request to register directory */
router.get('/register', (req,res)=>{
    res.render('register');
})

/* export router instance to use it in other files */
module.exports = router; 