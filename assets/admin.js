jQuery(document).ready(function ($) {
    let allTitlesValid = true;

    const { __ } = wp.i18n;

    // Initialize Select2 on multi-select dropdowns
    $('#post_types').select2({
        placeholder: __('Select post types', 'eazly-content-generator'),
        allowClear: true,
        width: '100%'
    });

    $('#content_elements').select2({
        placeholder: __('Select content elements', 'eazly-content-generator'),
        allowClear: true,
        width: '100%'
    });
    // Notification helper function
    function showNotification(message, type = 'success') {
        const $notification = $('#eazly-notification');
        const $message = $('#eazly-notification-message');

        // Remove existing classes
        $notification.removeClass('notice-success notice-error notice-warning notice-info');

        // Add appropriate class based on type
        $notification.addClass('notice-' + type);

        // Set message (preserve line breaks)
        $message.html(message.replace(/\n/g, '<br>'));

        // Show notification
        $notification.slideDown();

        // Auto-hide after 5 seconds for success messages
        if (type === 'success') {
            setTimeout(function () {
                $notification.slideUp();
            }, 5000);
        }
    }

    function hideNotification() {
        $('#eazly-notification').slideUp();
    }

    // Step 1: Submit form
    $('#eazly-form-step-one').on('submit', function (e) {
        e.preventDefault();

        const formData = {
            action: 'eazly_load_step_two',
            nonce: eazlyContent.nonce,
            num_posts: $('#num_posts').val(),
            post_types: $('#post_types').val(),
            title_generation: $('#title_generation').val(),
            content_elements: $('#content_elements').val()
        };

        $.ajax({
            url: eazlyContent.ajaxUrl,
            type: 'POST',
            data: formData,
            beforeSend: function () {
                $('#eazly-form-step-one button[type="submit"]').prop('disabled', true).text(__('Loading...', 'eazly-content-generator'));
            },
            success: function (response) {
                if (response.success) {
                    $('#eazly-step-one').hide();
                    $('#eazly-step-two-content').html(response.data.html);
                    $('#eazly-step-two').show();
                    initStepTwo();
                }
            },
            error: function () {
                showNotification(__('An error occurred. Please try again.', 'eazly-content-generator'), 'error');
            },
            complete: function () {
                $('#eazly-form-step-one button[type="submit"]').prop('disabled', false).text(__('Continue to Step 2', 'eazly-content-generator'));
            }
        });
    });

    // Initialize step two functionality
    function initStepTwo() {
        // Back button
        $('#eazly-back-button').on('click', function () {
            $('#eazly-step-two').hide();
            $('#eazly-step-one').show();
        });

        // Post type change - regenerate title
        $(document).on('change', '.eazly-post-type', function () {
            const index = $(this).data('index');
            const postType = $(this).val();
            const titleInput = $('input[name="posts[' + index + '][title]"]');
            const titleGeneration = $('input[name="title_generation"]').val();

            regenerateTitle(titleInput, postType, titleGeneration, index);
        });

        // Manual title edit - check availability
        let titleCheckTimeout;
        $(document).on('input', '.eazly-post-title', function () {
            const $input = $(this);
            const index = $input.data('index');
            const title = $input.val();
            const postType = $('select[name="posts[' + index + '][post_type]"]').val();

            clearTimeout(titleCheckTimeout);
            titleCheckTimeout = setTimeout(function () {
                checkTitleAvailability(title, postType, index);
            }, 500);
        });

        // Generate posts
        $('#eazly-form-step-two').on('submit', function (e) {
            e.preventDefault();

            if (!allTitlesValid) {
                showNotification(__('Please resolve title conflicts before generating posts.', 'eazly-content-generator'), 'warning');
                return;
            }

            const formData = $(this).serialize();
            formData.action = 'eazly_generate_posts';
            formData.nonce = eazlyContent.nonce;

            $.ajax({
                url: eazlyContent.ajaxUrl,
                type: 'POST',
                data: formData + '&action=eazly_generate_posts&nonce=' + eazlyContent.nonce,
                beforeSend: function () {
                    $('#eazly-generate-button').prop('disabled', true).text(__('Generating...', 'eazly-content-generator'));
                },
                success: function (response) {
                    if (response.success) {
                        let message = response.data.message;
                        if (response.data.posts.length > 0) {
                            message += '\n\n' + __('Created posts:', 'eazly-content-generator') + '\n';
                            response.data.posts.forEach(function (post) {
                                message += '- ' + post.title + '\n';
                            });
                        }
                        showNotification(message, 'success');

                        // Reset to step 1
                        $('#eazly-step-two').hide();
                        $('#eazly-step-one').show();
                        $('#eazly-form-step-one')[0].reset();
                        // Reset Select2
                        $('#post_types').val(null).trigger('change');
                        $('#content_elements').val(null).trigger('change');
                    }
                },
                error: function () {
                    showNotification(__('An error occurred while generating posts.', 'eazly-content-generator'), 'error');
                },
                complete: function () {
                    $('#eazly-generate-button').prop('disabled', false).text(__('Generate Posts', 'eazly-content-generator'));
                }
            });
        });
    }

    // Check title availability
    function checkTitleAvailability(title, postType, index) {
        $.ajax({
            url: eazlyContent.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eazly_check_title',
                nonce: eazlyContent.nonce,
                title: title,
                post_type: postType
            },
            success: function (response) {
                if (response.success) {
                    updateStatusIcon(index, response.data.available);
                }
            }
        });
    }

    // Update status icon
    function updateStatusIcon(index, available) {
        const $icon = $('.eazly-status[data-index="' + index + '"]');

        if (available) {
            $icon.removeClass('dashicons-no').addClass('dashicons-yes').css('color', '#46b450');
        } else {
            $icon.removeClass('dashicons-yes').addClass('dashicons-no').css('color', '#dc3232');
        }

        checkAllTitlesValid();
    }

    // Check if all titles are valid
    function checkAllTitlesValid() {
        allTitlesValid = $('.eazly-status.dashicons-no').length === 0;
        $('#eazly-generate-button').prop('disabled', !allTitlesValid);
    }

    // Regenerate title when post type changes
    function regenerateTitle(titleInput, postType, method, index) {
        const currentTitle = titleInput.val();

        // Simple regeneration logic (can be enhanced)
        let newTitle = '';

        if (method === 'sequential') {
            newTitle = postType.charAt(0).toUpperCase() + postType.slice(1) + ' ' + (index + 1);
        } else if (method === 'generic') {
            const genericTitles = ['Home', 'About', 'Services', 'Contact', 'Blog', 'Shop', 'Products', 'FAQ', 'Team', 'Portfolio'];
            newTitle = genericTitles[Math.floor(Math.random() * genericTitles.length)] + ' ' + (index + 1);
        } else if (method === 'lorem') {
            const loremWords = ['Lorem', 'Ipsum', 'Dolor', 'Sit', 'Amet', 'Consectetur'];
            const wordCount = 3 + Math.floor(Math.random() * 2);
            const words = [];
            for (let i = 0; i < wordCount; i++) {
                words.push(loremWords[Math.floor(Math.random() * loremWords.length)]);
            }
            newTitle = words.join(' ');
        }

        titleInput.val(newTitle);
        checkTitleAvailability(newTitle, postType, index);
    }
});