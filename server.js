/* main */
const express = require('express');
const router = express.Router();
const app = express();
const mongoose = require('mongoose');
const expressEjsLayout = require('express-ejs-layouts');
const session = require('express-session');
const flash = require('connect-flash');

//mongoose
mongoose.connect('mongodb://localhost/users',{useNewUrlParser: true, useUnifiedTopology : true})
.then(() => console.log('Connected to db'))
.catch((err) => console.log(err));

//EJS
app.set('view engine','ejs');
app.use(expressEjsLayout);

//BodyParser
app.use(express.urlencoded({extended : false}));

//Routes
app.use('/',require('./routes/index'));
app.use('/users',require('./routes/users'));

app.listen(3000); 

console.log("Connected to port 3000");