                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            // Employee list DataTable
            if ($('#employeeTable').length) {
                $('#employeeTable').DataTable({
                    "pageLength": <?php echo RECORDS_PER_PAGE; ?>,
                    "language": {
                        "search": "Search employees:",
                        "lengthMenu": "Show _MENU_ employees per page",
                        "info": "Showing _START_ to _END_ of _TOTAL_ employees",
                        "infoEmpty": "No employees found",
                        "infoFiltered": "(filtered from _MAX_ total employees)",
                        "emptyTable": "No employees found",
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        }
                    },
                    "columnDefs": [
                        { "orderable": false, "targets": [8] } // Disable sorting on Actions column
                    ]
                });
            }
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Form validation
            $('form').on('submit', function(e) {
                let isValid = true;
                const form = $(this);
                
                // Clear previous error states
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                
                // Validate required fields
                form.find('input[required], select[required], textarea[required]').each(function() {
                    if (!$(this).val().trim()) {
                        $(this).addClass('is-invalid');
                        $(this).after('<div class="invalid-feedback">This field is required.</div>');
                        isValid = false;
                    }
                });
                
                // Validate email format
                form.find('input[type="email"]').each(function() {
                    const email = $(this).val().trim();
                    if (email && !isValidEmail(email)) {
                        $(this).addClass('is-invalid');
                        $(this).after('<div class="invalid-feedback">Please enter a valid email address.</div>');
                        isValid = false;
                    }
                });
                
                // Validate phone format
                form.find('input[type="tel"]').each(function() {
                    const phone = $(this).val().trim();
                    if (phone && !isValidPhone(phone)) {
                        $(this).addClass('is-invalid');
                        $(this).after('<div class="invalid-feedback">Please enter a valid phone number.</div>');
                        isValid = false;
                    }
                });
                
                // Validate salary
                form.find('input[name="salary"]').each(function() {
                    const salary = parseFloat($(this).val());
                    if (salary && (salary < 0 || salary > 999999)) {
                        $(this).addClass('is-invalid');
                        $(this).after('<div class="invalid-feedback">Please enter a valid salary amount.</div>');
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.is-invalid').first().offset().top - 100
                    }, 500);
                }
            });
            
            // Real-time validation
            $('input, select, textarea').on('blur change', function() {
                const field = $(this);
                const value = field.val().trim();
                
                // Clear previous validation state
                field.removeClass('is-invalid');
                field.next('.invalid-feedback').remove();
                
                // Validate based on field type
                if (field.prop('required') && !value) {
                    field.addClass('is-invalid');
                    field.after('<div class="invalid-feedback">This field is required.</div>');
                } else if (field.attr('type') === 'email' && value && !isValidEmail(value)) {
                    field.addClass('is-invalid');
                    field.after('<div class="invalid-feedback">Please enter a valid email address.</div>');
                } else if (field.attr('type') === 'tel' && value && !isValidPhone(value)) {
                    field.addClass('is-invalid');
                    field.after('<div class="invalid-feedback">Please enter a valid phone number.</div>');
                }
            });
            
            // Search functionality
            $('#searchInput').on('keyup', function() {
                const searchValue = $(this).val();
                // Auto-submit search form after 1 second of inactivity
                clearTimeout(window.searchTimeout);
                window.searchTimeout = setTimeout(function() {
                    if (searchValue.length >= 2 || searchValue.length === 0) {
                        $('#searchForm').submit();
                    }
                }, 1000);
            });
            
            // Confirm delete
            $('.delete-btn').on('click', function(e) {
                if (!confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
            
            // Format currency input
            $('input[name="salary"]').on('input', function() {
                let value = $(this).val().replace(/[^\d.]/g, '');
                if (value && !isNaN(value)) {
                    $(this).val(parseFloat(value).toFixed(2));
                }
            });
        });
        
        // Utility functions
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        function isValidPhone(phone) {
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
        }
        
        function formatPhoneNumber(phone) {
            const cleaned = phone.replace(/\D/g, '');
            if (cleaned.length === 10) {
                return cleaned.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            }
            return phone;
        }
        
        // Auto-format phone number input
        $('input[type="tel"]').on('input', function() {
            let value = $(this).val();
            const formatted = formatPhoneNumber(value);
            $(this).val(formatted);
        });
    </script>
</body>
</html>