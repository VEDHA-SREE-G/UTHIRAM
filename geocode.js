const NodeGeocoder = require('node-geocoder');

// Set up Nominatim geocoder with a custom user agent
const options = {
    provider: 'openstreetmap', 
    httpAdapter: 'https', 
    apiKey: null,
    formatter: null,
    // Custom user agent to identify your application
    userAgent: 'Uthiram/1.0 (info.uthiram@gmail.com)' // replace with your app name and email
};

const geocoder = NodeGeocoder(options);

async function geocodeAddress(address) {
    const result = await geocoder.geocode(address);
    if (result.length > 0) {
        return {
            latitude: result[0].latitude,
            longitude: result[0].longitude
        };
    } else {
        throw new Error('Address not found');
    }
}

async function getLatLong(address) {
    try {
        const location = await geocodeAddress(address);
        return [location.latitude, location.longitude];
    } catch (error) {
        console.error("Error getting latitude and longitude:", error);
        return [null, null];
    }
}

module.exports = { getLatLong };
