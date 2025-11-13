/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

// js goes here
import $ from 'jquery';
import 'bootstrap';
import 'datatables.net';
import 'datatables.net-bs5';

$(document).ready(function () {
    $('#dtHere').DataTable();
});
