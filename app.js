const express = require('express');
const bodyParser = require('body-parser');
const multer = require('multer');
const nodemailer = require('nodemailer');
const fs = require('fs');
const mysql = require('mysql');
const axios = require('axios');

const app = express();
const PORT = 3000;

// Database connection
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'udhiram'
});

db.connect(err => {
    if (err) {
        console.error('Database connection failed:', err);
        return; // Exit if database connection fails
    }
    console.log('Connected to the database');
});

// Setup multer for file uploads
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        cb(null, 'uploads/'); // Specify your upload directory
    },
    filename: (req, file, cb) => {
        cb(null, file.originalname); // Use original file name
    }
});
const upload = multer({ storage: storage });

// Configure nodemailer
const transporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
        user: 'info.uthiram@gmail.com',
        pass: 'ljqc bhaq sfcy yyav' // Use app passwords or OAuth for better security
    }
});

// Replace with your actual OpenCage API key
const OPENCAGE_API_KEY = '9e78f42a42154a82bfc90a1e8c03ee66';

// Function to get latitude and longitude using OpenCage
async function getLatLong(address) {
    try {
        const response = await axios.get('https://api.opencagedata.com/geocode/v1/json', {
            params: {
                q: address,
                key: OPENCAGE_API_KEY,
                limit: 1,
                language: 'en'
            }
        });

        if (response.data && response.data.results.length > 0) {
            const { lat, lng } = response.data.results[0].geometry;
            return [lat, lng];
        } else {
            throw new Error('No results found');
        }
    } catch (error) {
        console.error('Error getting latitude and longitude:', error.message);
        throw error; // Rethrow error for handling in the route
    }
}

// Middleware
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
app.use(express.static('uploads')); // Serve static files from uploads directory

// Define a route for the root URL
app.get('/', (req, res) => {
    res.sendFile(__dirname + '/requirement.html'); // Serve your HTML file
});

// Function to validate email addresses
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
}

// Define the form submission route
// Define the form submission route
// Define the form submission route
app.post('/submit-form', upload.fields([
    { name: 'report', maxCount: 1 },
    { name: 'a_copy', maxCount: 1 },
    { name: 'p_copy', maxCount: 1 }
]), async (req, res) => {
    console.log('Form submission received:', req.body);

    const {
        attender, phone, a_address, a_aadhar, patient, p_aadhar, group, units, doctor,
        regno, desig, hospital, address, date
    } = req.body;

    const report = req.files['report'] ? req.files['report'][0] : null;
    const a_copy = req.files['a_copy'] ? req.files['a_copy'][0] : null;
    const p_copy = req.files['p_copy'] ? req.files['p_copy'][0] : null;

    if (!report || !a_copy || !p_copy) {
        return res.status(400).send("All files are required.");
    }

    try {
        // Get latitude and longitude for the hospital
        const [hospitalLat, hospitalLon] = await getLatLong(address);

        // Insert data into MySQL database
        const sql = "INSERT INTO patient (attender, phone, patient, blood, units, doctor, regno, hospital, address, report, a_address, a_aadhar, a_copy, p_aadhar, p_copy, desig, date, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
        db.query(sql, [
            attender, phone, patient, group, units, doctor, regno, hospital, address,
            fs.readFileSync(report.path), a_address, a_aadhar, fs.readFileSync(a_copy.path),
            p_aadhar, fs.readFileSync(p_copy.path), desig, date, hospitalLat, hospitalLon
        ], (err, result) => {
            if (err) {
                console.error("Error inserting into database:", err);
                return res.status(500).send("Internal Server Error: Database insertion failed.");
            }

            const patientId = result.insertId; // Get the ID of the newly inserted patient

            // Fetch nearby blood banks
            const fetchNearbyBloodBanks = `
                SELECT * FROM blood_bank 
                WHERE (POW(latitude - ?, 2) + POW(longitude - ?, 2)) < ?;`;
            const radius = Math.pow(100 / 111.32, 2); // 100 km radius, convert to degrees
            db.query(fetchNearbyBloodBanks, [hospitalLat, hospitalLon, radius], async (err, bloodBanks) => {
                if (err) {
                    console.error("Error fetching blood banks:", err);
                    return res.status(500).send("Internal Server Error: Could not fetch blood banks.");
                }

                console.log(`Number of nearby blood banks found: ${bloodBanks.length}`); // Debugging log

                if (bloodBanks.length === 0) {
                    return res.status(404).send("No nearby blood banks found.");
                }

                // Prepare email options for the first nearest blood bank
                const nearestBank = bloodBanks[0]; // Get the first nearest blood bank
                const emailAddress = nearestBank.phone; // Ensure this is the correct field for email addresses
                console.log(`Attempting to send email to: ${emailAddress}`); // Log email attempt

                if (!validateEmail(emailAddress)) {
                    console.error(`Invalid email address: ${emailAddress}`);
                    return res.status(400).send("Invalid email address for nearest blood bank.");
                }

                const mailOptions = {
                    from: 'info.uthiram@gmail.com',
                    to: emailAddress,
                    subject: 'Patient Form Submission',
                    html: `
                        <h2>New Patient Request</h2>
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
                        <p>Units Needed: ${units}</p>
                        <p>Date and Time of Operation:${date}</p>
                        <p><a href="http://localhost/udhiram/login.html" target="_blank">Click here to login!</a></p>
                    `,
                    attachments: [
                        {
                            filename: report.originalname,
                            path: report.path,
                            contentType: 'application/pdf'
                        },
                        {
                            filename: a_copy.originalname,
                            path: a_copy.path,
                            contentType: 'application/pdf'
                        },
                        {
                            filename: p_copy.originalname,
                            path: p_copy.path,
                            contentType: 'application/pdf'
                        }
                    ]
                };

                // Send email
                try {
                    await transporter.sendMail(mailOptions);
                    console.log(`Email sent to: ${emailAddress}`);

                    // Insert into patient_bank table
                    const bankId = nearestBank.id; // Assuming nearestBank has an 'id' field
                    const insertPatientBankSQL = "INSERT INTO patient_bank (patient_id, bank_id, flag) VALUES (?, ?, ?)";
                    db.query(insertPatientBankSQL, [patientId, bankId, 1], (err) => {
                        if (err) {
                            console.error("Error inserting into patient_bank table:", err);
                            return res.status(500).send("Internal Server Error: Could not update patient_bank table.");
                        }
                        res.send("Form submitted and email sent to the nearest blood bank successfully.");
                    });

                } catch (error) {
                    console.error(`Error sending email to ${emailAddress}:`, error.message);
                    res.status(500).send("Internal Server Error: Could not send email.");
                }
            });
        });
    } catch (error) {
        console.error("Error:", error.message);
        res.status(500).send("Internal Server Error: Could not get latitude and longitude.");
    }
});


// Start the server
app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
