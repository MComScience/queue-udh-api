var host = 'http://10.188.231.11:3000';
var socket = io(host);
$(function () {
    socket.on('connect', () => {
        console.warn('connect: ' + socket.id);
    }).on('connect_error', (error) => {
        console.warn('connect_error: ' + error);
    }).on('disconnect', (reason) => {
        console.warn('disconnect: ' + reason);
    }).on('connect_timeout', (timeout) => {
        console.warn('connect_timeout: ' + timeout);
    }).on('reconnect', (attemptNumber) => {
        console.warn('reconnect: ' + attemptNumber);
    }).on('reconnect_error', (error) => {
        console.warn('reconnect_error: ' + error);
    });
});