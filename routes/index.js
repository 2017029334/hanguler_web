const express = require('express');
const router  = express.Router();
const {ensureAuthenticated} = require("../config/auth.js")

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

/* go to dashboard after login */
router.get('/dashboard',ensureAuthenticated,(req,res)=>{
    res.render('dashboard',{
        user: req.user
    }); 
})

/* export router instance to use it in other files */
module.exports = router; 