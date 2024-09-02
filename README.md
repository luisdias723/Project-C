# Project C
This project was made with Vue.js and Laravel. It was a beta test version for tests and to begin the project. The main pupose of this code is to understand the methodologies that I use.

## Architecture

In this project I used the MVC architecture. I separated my application in two components, backend and frontend.

## Frontend
In frontend I used Vue.js and it's in the resource folder. I created new views and I also used the vue router to create the pages we needed. In this version of the project I didn't use components however I think using components is the best methodology because with it we can reuse the components the times we need and we don't need to repeat the same code n times.

## Backend
In backend I used Laravel and it was where I had my Model and my Controllers. I created controllers with some of the CRUD functions and, if needed, I also created functions to retrieve the data we wanted or to make the desired operations with database. In the controllers I used the models to make query’s to database. I also used the resources, migrations, seeders and other operations provided by the Laravel framework.

## API
To connect frontend and backend I used the laravel as API where I declared the expected behaviour of each route. For example when the route is "login" it should go to controller "authController" to the function "login". For the simple CRUD operations I used the apiresource provided by Laravel. In the frontend to get the data from a specific endpoint I used axios to make my requests and get the desired responses.
