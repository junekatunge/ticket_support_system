// Create Ticket Modal JavaScript
$(document).ready(function() {
    let aiSuggestionData = null;
    let aiAnalysisTimeout = null;

    // AI Suggestion functionality
    function analyzeTicketContent() {
        const title = $('#ticketSubject').val();
        const description = $('#ticketComment').val();

        // Only analyze if we have at least 5 characters
        if ((title + description).length < 5) {
            $('#aiSuggestionBox').slideUp();
            return;
        }

        // Clear previous timeout
        clearTimeout(aiAnalysisTimeout);

        // Set new timeout to avoid too many requests
        aiAnalysisTimeout = setTimeout(function() {
            $.ajax({
                url: 'get-ai-suggestion.php',
                method: 'POST',
                data: {
                    title: title,
                    description: description
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.suggestion) {
                        aiSuggestionData = response.suggestion;
                        displayAISuggestions(response.suggestion);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AI Analysis Error:', error);
                }
            });
        }, 1000); // Wait 1 second after user stops typing
    }

    function displayAISuggestions(suggestion) {
        // Update suggestion box
        $('#aiCategorySuggestion').text(suggestion.category);
        $('#aiCategoryReason').text(suggestion.category_reason);
        $('#aiPrioritySuggestion').text(suggestion.priority);
        $('#aiPriorityReason').text(suggestion.priority_reason);

        // Show the suggestion box with animation
        $('#aiSuggestionBox').slideDown();
    }

    // Listen for changes in subject and comment fields
    $('#ticketSubject, #ticketComment').on('input', function() {
        analyzeTicketContent();
    });

    // Accept AI suggestions
    $('#acceptAISuggestions').on('click', function() {
        if (aiSuggestionData) {
            // Set category
            $('#ticketCategory').val(aiSuggestionData.category.toLowerCase()).trigger('change');
            // Set priority
            $('#ticketPriority').val(aiSuggestionData.priority.toLowerCase()).trigger('change');

            // Show AI badges
            $('#aiCategoryBadge').fadeIn();
            $('#aiPriorityBadge').fadeIn();

            // Add visual feedback
            $('#ticketCategory, #ticketPriority').css('border-color', '#8B4513');

            // Hide suggestion box
            $('#aiSuggestionBox').slideUp();

            // Show success message
            showSuccessMessage('AI suggestions applied successfully!');
        }
    });

    // Dismiss AI suggestions
    $('#dismissAISuggestions').on('click', function() {
        $('#aiSuggestionBox').slideUp();
    });

    // Reset AI badges when user manually changes
    $('#ticketCategory').on('change', function() {
        if (aiSuggestionData && $(this).val() !== aiSuggestionData.category.toLowerCase()) {
            $('#aiCategoryBadge').fadeOut();
            $(this).css('border-color', '');
        }
    });

    $('#ticketPriority').on('change', function() {
        if (aiSuggestionData && $(this).val() !== aiSuggestionData.priority.toLowerCase()) {
            $('#aiPriorityBadge').fadeOut();
            $(this).css('border-color', '');
        }
    });

    // Reset form when modal is closed
    $('#createTicketModal').on('hidden.bs.modal', function() {
        $('#ticketForm')[0].reset();
        $('#aiSuggestionBox').hide();
        $('#aiCategoryBadge, #aiPriorityBadge').hide();
        $('#ticketCategory, #ticketPriority').css('border-color', '');
        aiSuggestionData = null;
    });

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