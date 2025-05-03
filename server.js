const express = require('express');
const nodemailer = require('nodemailer');
const bodyParser = require('body-parser');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 3001;

app.use(cors());
app.use(bodyParser.json());

// Configure Nodemailer
const transporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
        user: 'info.uthiram@gmail.com', // Replace with your email
        pass: 'ljqc bhaq sfcy yyav', // Replace with your email password or App password
    },
});

// Function to send an email
const sendEmail = (bankDetails, bankDistrict, hospitalDetails, hospitalAddress, donate,date,group) => {
    const mailOptions = {
        from: 'info.uthiram@gmail.com',
        to: 'uthiramlogistics@gmail.com', // Replace with the recipient's email
        subject: 'Donation Alert',
        html: `
                            <h2>REQUEST ACCEPTED</h2>
                            <h3>DONOR DETAILS<h3>
                            <p>Blood Bank: ${bankDetails}</p>
                            <p>Address: ${bankDistrict}</p>
                            <p>Blood Group: ${group}</p>
                            <p>Units of Blood Donating: ${donate}</p>
                            <hr>
                            <h3>RECIPIENT DETAILS<h3>
                            <p>Hospital:${hospitalDetails}</p>
                            <p>Address: ${hospitalAddress}</p>
                            <p>Date and Time of Operation: ${date}</p>
                        `,
    };

    transporter.sendMail(mailOptions, (error, info) => {
        if (error) {
            console.error('Error sending email:', error);
        } else {
            console.log('Email sent:', info.response);
        }
    });
};

// Endpoint to handle donation notifications
// Endpoint to handle donation notifications
app.post('/send-email', (req, res) => {
    console.log('Email API called'); // Debug log
    console.log(req.body); // Debug log of the incoming data

    const { bankDetails, bankDistrict, hospitalDetails, hospitalAddress, donate,date,group } = req.body;

    if (!bankDetails || !hospitalDetails || !hospitalAddress || !bankDistrict || donate == null) {
        console.error('Missing required data.');
        return res.status(400).send('Missing required data.');
    }

    // Send email only if the donation is greater than 0
    if (donate > 0) {
        sendEmail(bankDetails, bankDistrict, hospitalDetails, hospitalAddress, donate,date,group);
        console.log('Email send triggered'); // Debug log
        return res.send('Email request received');
    } else {
        console.error('Donation amount must be greater than 0.');
        return res.status(400).send('Donation amount must be greater than 0.');
    }
});


app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});
