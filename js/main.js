
const getTeamMember = function (teamId){
    $.ajax({
        'url': './src/get-team-member.php',
        'type': 'post',
        'dataType': 'text',
        'data': {'id': teamId},
        'success': function(response){
            let result = JSON.parse(response);
            const options = [];

            for(let i = 0; i< result.length; i++){
                let option = `<option value="${result[i].id}">${result[i].user}</option>`;
                options.push(option);
            }

            $('#team-member-dropdown').html(options.join(''));

        }
    });
}