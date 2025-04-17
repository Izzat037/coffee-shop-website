document.addEventListener('DOMContentLoaded', function() {
    // Initialize all tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Product quantity selector
    const decrementButtons = document.querySelectorAll('.decrement');
    const incrementButtons = document.querySelectorAll('.increment');
    
    decrementButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.nextElementSibling;
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        });
    });
    
    incrementButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            let value = parseInt(input.value);
            input.value = value + 1;
        });
    });

    // Form validations
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Add to cart animation
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            button.innerHTML = '<i class="fas fa-check"></i> Added';
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                button.classList.remove('btn-success');
                button.classList.add('btn-primary');
            }, 2000);
        });
    });

    // Star rating system
    const ratingInputs = document.querySelectorAll('.rating-input');
    const ratingLabels = document.querySelectorAll('.rating-label');
    
    ratingLabels.forEach(label => {
        label.addEventListener('mouseenter', function() {
            const value = this.getAttribute('for').split('-')[1];
            
            ratingLabels.forEach(l => {
                const labelValue = l.getAttribute('for').split('-')[1];
                if (labelValue <= value) {
                    l.classList.add('text-warning');
                } else {
                    l.classList.remove('text-warning');
                }
            });
        });
    });
    
    const ratingContainer = document.querySelector('.rating-container');
    if (ratingContainer) {
        ratingContainer.addEventListener('mouseleave', function() {
            ratingLabels.forEach(l => {
                l.classList.remove('text-warning');
            });
            
            ratingInputs.forEach(input => {
                if (input.checked) {
                    const value = input.value;
                    
                    ratingLabels.forEach(l => {
                        const labelValue = l.getAttribute('for').split('-')[1];
                        if (labelValue <= value) {
                            l.classList.add('text-warning');
                        }
                    });
                }
            });
        });
    }
}); 