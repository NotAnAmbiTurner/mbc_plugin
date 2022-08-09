var mbcoptions = {
    valueNames: [ 'name', 'born' ],
    pagination: true,
    // Since there are no elements in the list, this will be used as template.
    item: '<li><h3 class="name"></h3><p class="born"></p></li>'
};

var mbcvalues = [
{
    mbcname: 'Jonny Strömberg',
    mbcborn: 1986
},
{
    mbcname: 'Jonas Arnklint',
    mbcborn: 1985
},
{
    mbcname: 'Martina Elm',
    mbcborn: 1986
}
];

var mbcUserList = new List('mbcusers', mbcoptions, mbcvalues);

mbcUserList.add({
    mbcname: 'Gustaf Lindqvist',
    mbcborn: 1983
});