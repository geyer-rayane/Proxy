const express = require('express');
const fs = require('fs');
const axios = require('axios');
const app = express();

const PORT = process.env.PORT || 3000;

// === CONFIGURATION ===
const appToken = 'WMwUQFvhdxLE6bBHBBFHA53cQO4dkjDUoSYD0FKe';
const userToken = 'GRs14Za3o2tVPaq2H3j4RCDgmrSaPNFTOtvARh7u';
const apiUrl = 'https://poc-glpi-dlog.vaucluse.fr/apirest.php/Ticket/';

app.use(express.json());

app.post('/proxy', async (req, res) => {
  const rawInput = JSON.stringify(req.body);
  const input = req.body;

  // === LOGGING ===
  const logFile = './log_debug.txt';
  const logData = `==== NOUVELLE REQUÊTE [${new Date().toISOString()}] ====\n`;
  const fullLog = `${logData}RAW INPUT:\n${rawInput}\n\nJSON DECODE:\n${JSON.stringify(input, null, 2)}\n\n`;

  fs.appendFileSync(logFile, fullLog);

  // === VÉRIFICATION ===
  if (!input || !input.title) {
    res.status(400).json({ error: 'Données invalides' });
    return;
  }

  // === CONSTRUCTION DES DONNÉES ===
  const data = {
    input: {
      name: input.title,
      content: input.description || 'Ticket envoyé depuis DLOG',
      itilcategories_id: 1,
      type: 1,
      requesttypes_id: 1,
    }
  };

  try {
    const response = await axios.post(apiUrl, data, {
      headers: {
        'App-Token': appToken,
        'Authorization': `user_token ${userToken}`,
        'Content-Type': 'application/json'
      }
    });

    // === LOG REPONSE ===
    const responseLog = `HTTP RESPONSE CODE: ${response.status}\nAPI RESPONSE:\n${JSON.stringify(response.data, null, 2)}\n\n`;
    fs.appendFileSync(logFile, responseLog);

    res.status(response.status).json(response.data);

  } catch (error) {
    const errLog = `ERROR:\n${error.message}\n${error.response?.data ? JSON.stringify(error.response.data) : ''}\n\n`;
    fs.appendFileSync(logFile, errLog);

    res.status(error.response?.status || 500).json({ error: 'Erreur lors de l’appel API', detail: error.response?.data || error.message });
  }
});

app.listen(PORT, () => {
  console.log(`Serveur en ligne sur le port ${PORT}`);
});
