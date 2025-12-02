import express from "express";
import cors from "cors";

// create a ne express application instance
const app = express();
// app configuration
app.use(express.urlencoded({ extended: false }));
app.use(express.json());
app.use(cors());

// route ping
app.get("/ping", (req, res) =>{
    res.send("pong");
});

app.listen(8080, () => {
    console.log("the server is now running on Port 8080");
});