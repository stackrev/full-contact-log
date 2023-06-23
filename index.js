const express = require('express');
const bodyParser = require('body-parser');

const app = express();

// Middleware to parse json
app.use(bodyParser.json());

let recent_history = [];

app.post('/api/data', (req, res) => {
  const { pid, date } = req.body;
  if(!pid) return;
  const jsonObject = JSON.parse(pid);
  const personId = jsonObject.personIds[0];

  const date1 = new Date(recent_history[personId]);
  const date2 = new Date(date1);

  if(recent_history[personId] && date2.getTime() - date1.getTime() < 3600 * 1000){
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
});

const port = process.env.PORT || 5000;

app.listen(port, () => {
  console.log(`Server is running on port ${port}`);
});
