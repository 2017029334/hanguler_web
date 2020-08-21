
const express = require('express');
const router = express.Router();
const User = require("../models/user.js");
const bcrypt = require('bcrypt');
const passport = require('passport');

/* login handle */
router.get('/login',(req,res)=>{
    res.render('login');
})
router.get('/register',(req,res)=>{
    res.render('register')
    })

/* register handle */
router.post('/register',(req,res)=>{

    /* extract elements from the form */
    const {name,email, password, password2} = req.body;
    let errors = [];
    console.log('Name: ' + name+ ' Email: ' + email+ ' Password:' + password);

    /* toast error message if any of the forms has not been filled */
    if(!name || !email || !password || !password2) 
    {
        errors.push({msg : "Please fill in all fields"})
    }

    /* check if passwords match */
    if(password !== password2) 
    {
        errors.push({msg : "passwords dont match"});
    }
    
    /* check if password is more than 6 characters */
    if(password.length < 6) 
    {
        errors.push({msg : 'password atleast 6 characters'})
    }

    /* re-render the register.ejs page and then send the appropriate data along with the errors array */
    if(errors.length > 0 ) 
    {
        res.render('register', {
            errors : errors,
            name : name,
            email : email,
            password : password,
            password2 : password2})
    } 
    else 
    {
        /* validation passed */
        User.findOne({email : email}).exec((err,user)=>{
            console.log(user);   
            if(user) 
            {
                errors.push({msg: 'email already registered'});
                res.render('register',{errors,name,email,password,password2}) 
            } 
            else 
            {
                const newUser = new User({
                    name : name,
                    email : email,
                    password : password
                });

                /* Generate hash password */
                bcrypt.genSalt(10,(err,salt)=> 
                bcrypt.hash(newUser.password,salt,
                    (err,hash)=> {
                        if(err) throw err;
                            //save password to hash
                            newUser.password = hash;
                        /* save users to db */
                        newUser.save()
                        /* with no error, redirect to login directory */
                        .then((value)=>{
                            console.log(value)
                            req.flash('success_msg','You have now registered!');
                            res.redirect('/users/login');
                        })
                        .catch(value=> console.log(value));
                        
                    }));

            }
        })
    }
})
    
/*  If the user has successfully logged in, they will be redirected to the dashboard directory (successRedirect).
    If the user does not log in successfully, redirect them to the login directory (failureRedirect).
    Get flash messages when an error occurs (failureFlash). */
router.post('/login',(req,res,next)=>{
    passport.authenticate('local',{
        successRedirect : '/dashboard',
        failureRedirect : '/users/login',
        failureFlash : true,
        })(req,res,next);
  })

//logout
router.get('/logout',(req,res)=>{
    req.logout();
    req.flash('success_msg','Now logged out');
    res.redirect('/users/login');
 })


module.exports  = router;