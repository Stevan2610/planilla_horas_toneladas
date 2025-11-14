// Axios global config
// Make sure axios is available (cdn included in HTML)
//Base apuntando AL index.php del backend
const API_BASE = 'http://localhost/planilla_horas_toneladas/backend/src';

axios.defaults.baseURL = API_BASE;
//axios.defaults.baseURL ='http://localhost/planilla_horas_toneladas/backend/src';
axios.defaults.withCredentials = true; // important to send session cookie
axios.defaults.headers.post['Content-Type'] = 'application/json';
