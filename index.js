const express = require('express');
const bodyParser = require('body-parser');
const path = require('path');
const cors = require('cors');
const app = express();
const http = require('http');
const socketIO = require('socket.io');

// Enable CORS for all routes
app.use(cors());

// Middleware to parse json
app.use(bodyParser.json());

// Serve static files from the "public" directory
app.use(express.static(path.join(__dirname, 'public')));

// Create an HTTP server
const server = http.createServer(app);

// Create a Socket.IO instance
const io = socketIO(server);

// Socket.IO connection handler
io.on('connection', (socket) => {
  console.log('Socket.IO connected');

  socket.on('message', (message) => {
    console.log(`Received message: ${message}`);
    // Handle the received message
  });

  socket.on('disconnect', () => {
    console.log('Socket.IO disconnected');
  });
});
let recent_history = [];

app.post('/api/data', (req, res) => {
  const { pid, date } = req.body;
  if (!pid) return;
  const jsonObject = JSON.parse(pid);
  const personId = jsonObject.personIds[0];

  const date1 = new Date(recent_history[personId]);
  const date2 = new Date(date1);

  if (recent_history[personId] && date2.getTime() - date1.getTime() < 3600 * 1000) {
    recent_history[personId] = date;
    res.json({ status: 'success', message: 'Data duplicated so ignored' });
    return;
  }
  recent_history[personId] = date;

  // Verify that both pid and date were provided
  if (!pid || !date) {
    res.status(400).json({ error: 'Request body must contain both a pid and a date' });
    return;
  }

  // Do something with the pid and date here
  console.log(personId, date);

  // Send a response back to the client
  res.json({ status: 'success', message: 'Data received successfully' });

  // Send an update to all connected socket.io clients
  io.emit('message', `${personId} visited Holimont.com at ${date}`);
});

const port = process.env.PORT || 5000;


server.listen(port, () => {
  console.log(`Server is running on port ${port}`);
});

