const express = require('express');
const bodyParser = require('body-parser');

const app = express();

// Middleware to parse json
app.use(bodyParser.json());

app.post('/api/data', (req, res) => {
  const { pid, date } = req.body;

  // Verify that both pid and date were provided
  if (!pid || !date) {
    res.status(400).json({ error: 'Request body must contain both a pid and a date' });
    return;
  }

  // Do something with the pid and date here
  console.log(pid, date);

  // Send a response back to the client
  res.json({ status: 'success', message: 'Data received successfully' });
});

const port = process.env.PORT || 5000;

app.listen(port, () => {
  console.log(`Server is running on port ${port}`);
});
