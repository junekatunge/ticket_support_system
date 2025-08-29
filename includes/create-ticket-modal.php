<?php
// Ensure we have teams data
if (!isset($teams) || empty($teams)) {
    require_once './src/team.php';
    $team = new Team();
    $teams = $team->findAll();
}
?>

<!-- Create Ticket Modal -->
<div class="modal fade" id="createTicketModal" tabindex="-1" aria-labelledby="createTicketModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header modal-header-gradient">
        <div style="position: relative; z-index: 1;">
          <h3 class="modal-title mb-2" id="createTicketModalLabel" style="font-weight: 700; letter-spacing: -0.025em;">
            Create New Support Ticket
          </h3>
          <p class="mb-0" style="opacity: 0.9; font-size: 0.95rem;">Fill in the details below to submit your support request</p>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; top: 1.5rem; right: 1.5rem;"></button>
      </div>
      <form method="POST" id="ticketForm">
        <div class="modal-body" style="padding: 1.5rem;">
          <!-- Condensed layout with two columns -->
          <div class="row g-3">
            <!-- Left Column - Requester Info -->
            <div class="col-md-6">
              <div class="mb-2">
                <h6 class="fw-bold mb-2" style="font-size: 0.95rem;">
                  <i class="fas fa-user-circle me-2 text-primary" style="font-size: 0.9rem;"></i>Requester Information
                </h6>
              </div>
              <div class="row g-2">
                <div class="col-12">
                  <div class="form-floating-custom" style="margin-bottom: 1rem;">
                    <label for="requesterName" style="font-size: 0.8rem;">Name</label>
                    <input type="text" class="form-control" id="requesterName" name="requester_name" required placeholder="Enter name" style="padding: 0.65rem; font-size: 0.9rem;">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-floating-custom" style="margin-bottom: 1rem;">
                    <label for="departmentName" style="font-size: 0.8rem;">Department</label>
                    <input type="text" class="form-control" id="departmentName" name="department_name" required placeholder="Enter department name" style="padding: 0.65rem; font-size: 0.9rem;">
                  </div>
                </div>
                <div class="col-6">
                  <div class="form-floating-custom" style="margin-bottom: 1rem;">
                    <label for="buildingName" style="font-size: 0.8rem;">Building</label>
                    <input type="text" class="form-control" id="buildingName" name="building_name" required placeholder="Building" style="padding: 0.65rem; font-size: 0.9rem;">
                  </div>
                </div>
                <div class="col-6">
                  <div class="form-floating-custom" style="margin-bottom: 1rem;">
                    <label for="roomNumber" style="font-size: 0.8rem;">Room</label>
                    <input type="text" class="form-control" id="roomNumber" name="room_number" required placeholder="Room #" style="padding: 0.65rem; font-size: 0.9rem;">
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Right Column - Ticket Details -->
            <div class="col-md-6">
              <div class="mb-2">
                <h6 class="fw-bold mb-2" style="font-size: 0.95rem;">
                  <i class="fas fa-ticket-alt me-2 text-primary" style="font-size: 0.9rem;"></i>Ticket Details
                </h6>
              </div>
              <div class="row g-2">
                <div class="col-12">
                  <div class="form-floating-custom" style="margin-bottom: 1rem;">
                    <label for="ticketSubject" style="font-size: 0.8rem;">Subject</label>
                    <input type="text" class="form-control" id="ticketSubject" name="subject" required placeholder="Enter subject" style="padding: 0.65rem; font-size: 0.9rem;">
                  </div>
                </div>
                <div class="col-6">
                  <div class="form-floating-custom" style="margin-bottom: 1rem;">
                    <label for="ticketCategory" style="font-size: 0.8rem;">Category</label>
                    <select class="form-select" id="ticketCategory" name="category" required style="padding: 0.65rem; font-size: 0.9rem;">
                      <option value="">--Select--</option>
                      <option value="hardware">💻 Hardware</option>
                      <option value="software">📱 Software</option>
                      <option value="network">🌐 Network</option>
                      <option value="email">📧 Email</option>
                      <option value="printer">🖨️ Printer</option>
                      <option value="account">👤 Account</option>
                      <option value="other">📋 Other</option>
                    </select>
                  </div>
                </div>
                <div class="col-6">
                  <div class="form-floating-custom" style="margin-bottom: 1rem;">
                    <label for="ticketPriority" style="font-size: 0.8rem;">Priority</label>
                    <select class="form-select" id="ticketPriority" name="priority" required style="padding: 0.65rem; font-size: 0.9rem;">
                      <option value="low" selected>🟢 Low</option>
                      <option value="medium">🟡 Medium</option>
                      <option value="high">🟠 High</option>
                      <option value="urgent">🔴 Urgent</option>
                    </select>
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-floating-custom" style="margin-bottom: 1rem;">
                    <label for="assignTeam" style="font-size: 0.8rem;">Team</label>
                    <select class="form-select" id="assignTeam" name="team_id" required style="padding: 0.65rem; font-size: 0.9rem;">
                      <option value="">--select--</option>
                      <?php
                      foreach($teams as $t) {
                        echo '<option value="'.$t->id.'">👥 '.$t->name.'</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Full Width - Comments Section -->
            <div class="col-12">
              <hr style="margin: 0.5rem 0; border-color: #e9ecef;">
            </div>
            <div class="col-md-6">
              <div class="form-floating-custom" style="margin-bottom: 0.75rem;">
                <label for="ticketComment" style="font-size: 0.8rem;">Comment</label>
                <textarea class="form-control" id="ticketComment" name="comment" rows="3" required placeholder="Enter comment" style="padding: 0.65rem; font-size: 0.9rem;"></textarea>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating-custom" style="margin-bottom: 0.75rem;">
                <label for="additionalInfo" style="font-size: 0.8rem;">Additional Info</label>
                <textarea class="form-control" id="additionalInfo" name="additional_info" rows="3" placeholder="Enter any additional information (optional)" style="padding: 0.65rem; font-size: 0.9rem;"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="background: #f8fafc; border: none; padding: 1.5rem 2rem;">
          <button type="button" class="btn btn-light-custom" data-bs-dismiss="modal">
            <i class="fas fa-arrow-left me-2"></i>Cancel
          </button>
          <button type="submit" name="submit_ticket" class="btn btn-gradient-primary">
            <i class="fas fa-paper-plane me-2"></i>Submit Ticket
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
/* Modal styles */
.modal-header-gradient {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  padding: 2rem 2rem 1.5rem;
  position: relative;
}

.modal-header-gradient::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8));
  backdrop-filter: blur(10px);
}

.btn-light-custom {
  background-color: #e9ecef;
  color: #495057;
  border: none;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 6px;
}

.btn-light-custom:hover {
  background-color: #dee2e6;
  color: #495057;
}

.btn-gradient-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
  color: white;
  font-weight: 600;
  padding: 0.5rem 1.5rem;
  border-radius: 6px;
}

.btn-gradient-primary:hover {
  background: linear-gradient(135deg, #5a6fd8, #6a4190);
  color: white;
}

.form-floating-custom label {
  color: #6c757d;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.form-floating-custom .form-control, .form-floating-custom .form-select {
  border: 1px solid #e9ecef;
  border-radius: 6px;
}

.form-floating-custom .form-control:focus, .form-floating-custom .form-select:focus {
  border-color: #D2B48C;
  box-shadow: 0 0 0 0.2rem rgba(210, 180, 140, 0.25);
}

.fa-user-circle.text-primary, .fa-ticket-alt.text-primary {
  color: #8B4513 !important;
}

.fa-arrow-left {
  color: #1e3a5f;
}

.fa-paper-plane {
  color: white;
}
</style>