// Create Ticket Modal JavaScript
$(document).ready(function() {
    // Handle form submission
    $('#ticketForm').on('submit', function(e) {
        e.preventDefault();
        console.log('Form submission intercepted by AJAX');
        
        // Show loading state
        const submitBtn = $(this).find('button[name="submit_ticket"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Creating Ticket...').prop('disabled', true);
        
        // Log form data for debugging
        var formData = $(this).serialize() + '&submit_ticket=1';
        console.log('Form data being sent:', formData);
        
        $.ajax({
            url: 'ticket.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // Close modal
                    $('#createTicketModal').modal('hide');
                    
                    // Show success message
                    showSuccessMessage(response.message);
                    
                    // Reset form
                    $('#ticketForm')[0].reset();
                    
                    // Reload the page to show the new ticket at the top
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    showErrorMessage(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', xhr, status, error);
                console.log('Response Text:', xhr.responseText);
                showErrorMessage('An error occurred while creating the ticket. Please try again. Error: ' + error);
            },
            complete: function() {
                // Reset button state
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
});

function showSuccessMessage(message) {
    // Create and show success alert
    const alert = $('<div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 80px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">' +
        '<i class="fas fa-check-circle me-2"></i>' + message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>');
    
    $('body').append(alert);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        alert.alert('close');
    }, 5000);
}

function showErrorMessage(message) {
    // Create and show error alert
    const alert = $('<div class="alert alert-danger alert-dismissible fade show position-fixed" style="top: 80px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">' +
        '<i class="fas fa-exclamation-triangle me-2"></i>' + message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>');
    
    $('body').append(alert);
    
    // Auto dismiss after 8 seconds
    setTimeout(function() {
        alert.alert('close');
    }, 8000);
}