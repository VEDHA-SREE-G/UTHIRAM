const express = require('express');
const nodemailer = require('nodemailer');
const bodyParser = require('body-parser');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 3002;

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
const sendEmail = (email,
    hospital,
    address,
    date,
    group,
  unit,
  patient,
  p_aadhar,
  attender,
  phone,
  a_aadhar,
  doctor,
  desig) => {
    const mailOptions = {
        from: 'info.uthiram@gmail.com',
                    to: email,
                    subject: 'Patient Form Submission',
                    html: `
                    <h2>New Donation Request</h2>                     
                       <h3>ATTENDER DETAILS<h3>
                        <p>Attender: ${attender}</p>
                        <p>Attender's Phone No: ${phone}</p>
                        <p>Attender's Aadhar No: ${a_aadhar}</p>
                        <hr>
                        <h3>PATIENT DETAILS<h3>
                        <p>Patient:${patient}</p>
                        <p>Patient's Aadhar No: ${p_aadhar}</p>
                        <hr>
                        <h3>DOCTOR DETAILS<h3>
                        <p>Doctor: ${doctor}</p>
                        <p>Designation: ${desig}</p>
                        <p>Hospital: ${hospital}</p>
                        <p>Address: ${address}</p>
                        <hr>
                        <h3>REQUIREMENT DETAILS<h3>
                        <p>Blood Group: ${group}</p>
                        <p>Units Needed: ${unit}</p>
                        <p>Date and Time of Operation:${date}</p>
                        <p><a href="http://localhost/udhiram/login.html" target="_blank">Click here to login!</a></p>
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
app.post('/send', (req, res) => {
    console.log('Email API called'); // Debug log
    console.log(req.body); // Debug log of the incoming data

    const {email,
        hospital,
        address,
        date,
        group,
      unit,
      patient,
      p_aadhar,
      attender,
      phone,
      a_aadhar,
      doctor,
      desig } = req.body;
    // Send email only if the donation is greater than 0
        sendEmail(email,
            hospital,
            address,
            date,
            group,
          unit,
          patient,
          p_aadhar,
          attender,
          phone,
          a_aadhar,
          doctor,
          desig);
        console.log('Email send triggered'); // Debug log
        return res.send('Email request received');
});


app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});
