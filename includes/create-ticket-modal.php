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
                <div class="col-12">
                  <div class="form-floating-custom" style="margin-bottom: 1rem;">
                    <label for="assignMember" style="font-size: 0.8rem;">👤 Assign to Team Member</label>
                    <select class="form-select" id="team-member-dropdown" name="team_member" style="padding: 0.65rem; font-size: 0.9rem;">
                      <option value="">--Optional: Select a team member--</option>
                      <?php
                      // Get all users from database
                      $db = Database::getInstance();
                      $sql = "SELECT id, name FROM users WHERE name IS NOT NULL AND name != '' ORDER BY name ASC";
                      $result = $db->query($sql);
                      if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                          if (!empty($row['name'])) {
                            echo '<option value="'.$row['id'].'">👤 '.htmlspecialchars($row['name']).'</option>';
                          }
                        }
                      }
                      ?>
                    </select>
                    <small class="text-muted" style="font-size: 0.75rem;">Optional: Assign to a specific team member</small>
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
:root {
  --treasury-navy: #1e3a5f;
  --treasury-gold: #c9a96e;
  --treasury-green: #2d5a3d;
  --treasury-blue: #4a90a4;
  --treasury-amber: #b8860b;
  --treasury-burgundy: #722f37;
  --treasury-dark: #2c3e50;
  --treasury-light: #f8f9fc;
  --treasury-brown: #8B4513;
  --treasury-tan: #D2B48C;
  --kenya-red: #922529;
  --kenya-green: #008C51;
}

/* Modal styles */
.modal-header-gradient {
  background: linear-gradient(135deg, var(--treasury-brown), var(--treasury-dark));
  color: white;
  border: none;
  padding: 2rem 2rem 1.5rem;
  position: relative;
  box-shadow: 0 4px 15px rgba(139, 69, 19, 0.3);
}

.modal-header-gradient::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(139, 69, 19, 0.9), rgba(44, 62, 80, 0.9));
  backdrop-filter: blur(10px);
}

.btn-light-custom {
  background-color: var(--treasury-light);
  color: var(--treasury-navy);
  border: 2px solid var(--treasury-tan);
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.btn-light-custom:hover {
  background-color: var(--treasury-tan);
  color: var(--treasury-brown);
  border-color: var(--treasury-brown);
}

.btn-gradient-primary {
  background: linear-gradient(135deg, var(--treasury-brown), var(--treasury-burgundy));
  border: none;
  color: white;
  font-weight: 600;
  padding: 0.5rem 1.5rem;
  border-radius: 6px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(139, 69, 19, 0.3);
}

.btn-gradient-primary:hover {
  background: linear-gradient(135deg, var(--treasury-burgundy), var(--treasury-dark));
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 69, 19, 0.4);
}

.form-floating-custom label {
  color: var(--treasury-navy);
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.form-floating-custom .form-control, .form-floating-custom .form-select {
  border: 2px solid var(--treasury-tan);
  border-radius: 6px;
  transition: all 0.3s ease;
}

.form-floating-custom .form-control:focus, .form-floating-custom .form-select:focus {
  border-color: var(--treasury-brown);
  box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
  outline: none;
}

.fa-user-circle.text-primary, .fa-ticket-alt.text-primary {
  color: var(--treasury-gold) !important;
}

.fa-arrow-left {
  color: var(--treasury-navy);
}

.fa-paper-plane {
  color: white;
}

.modal-content {
  border: none;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(139, 69, 19, 0.2);
}

.modal-footer {
  background: var(--treasury-light);
  border: none;
  padding: 1.5rem 2rem;
  border-radius: 0 0 12px 12px;
}

/* Kenya flag accent */
.modal-title::after {
  content: '';
  display: inline-block;
  width: 4px;
  height: 20px;
  background: linear-gradient(to bottom, var(--kenya-red) 33%, var(--treasury-dark) 33% 66%, var(--kenya-green) 66%);
  margin-left: 10px;
  vertical-align: middle;
  border-radius: 2px;
}
</style>