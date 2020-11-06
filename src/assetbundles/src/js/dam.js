// Import the plugins
const Uppy = require('@uppy/core')
const XHRUpload = require('@uppy/xhr-upload')
const Dashboard = require('@uppy/dashboard')
// const Tus = require('@uppy/tus')

// And their styles (for UI plugins)
// With webpack and `style-loader`, you can require them like this:
require('@uppy/core/dist/style.css')
require('@uppy/dashboard/dist/style.css')

window.Uppy = Uppy;
window.XHRUpload = XHRUpload;
window.Dashboard = Dashboard;




