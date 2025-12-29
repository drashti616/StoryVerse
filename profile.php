<?php
// FILE: profile.php
// User profile page to display information, allow profile picture management, and account deletion.

session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login if the user is not logged in.
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

require_once 'includes/header.php';
require_once 'includes/db_config.php';

$user_id = $_SESSION['user_id'];
$user_info = null;

// Fetch user information including the profile_image_path
$sql = "SELECT username, email, role, created_at, profile_image_path FROM users WHERE id = :user_id";
if ($stmt = $pdo->prepare($sql)) {
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Get the profile image path, or use a default placeholder (a simple user icon SVG)
$profile_image_src = $user_info['profile_image_path'] ? htmlspecialchars($user_info['profile_image_path']) : '';
?>

<!-- Cropper.js CSS and JS from CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<h1 class="text-4xl font-bold text-blue-500 text-center mb-6">User Profile</h1>
<div class="bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-xl mx-auto">
    <?php if ($user_info): ?>
        <div class="space-y-6">
            <!-- Profile Picture Section -->
            <div class="flex flex-col items-center space-y-4">
                <!-- Profile picture display area with click listener -->
                <div id="profile-picture-container" class="cursor-pointer relative w-32 h-32 rounded-full border-4 border-blue-500 overflow-hidden group <?php echo $profile_image_src ? '' : 'bg-gray-700'; ?>">
                    <?php if ($profile_image_src): ?>
                        <img id="profile-picture" src="<?php echo $profile_image_src . '?' . time(); ?>" alt="Profile Picture" class="w-full h-full object-cover transition-transform duration-300 transform group-hover:scale-110">
                        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <span class="text-white text-sm font-semibold">Click to Preview</span>
                        </div>
                    <?php else: ?>
                        <!-- Default placeholder icon for no profile picture -->
                        <div id="default-profile-icon" class="w-full h-full flex items-center justify-center text-white text-5xl">
                            <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex flex-wrap gap-2 justify-center">
                    <!-- The file input is hidden and triggered by the label -->
                    <input type="file" name="profile_picture_file" id="profile_picture_input" accept="image/*" class="hidden">
                    <label for="profile_picture_input" class="cursor-pointer inline-flex items-center justify-center px-4 py-2 border border-transparent text-base font-medium rounded-full text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-300">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </label>

                    <!-- Form to remove a picture -->
                    <form id="remove-form" action="upload_profile_picture.php" method="post" class="<?php echo $profile_image_src ? 'inline-block' : 'hidden'; ?>">
                        <input type="hidden" name="remove_submit" value="1">
                        <button type="button" id="remove-button" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-base font-medium rounded-full text-white bg-red-600 hover:bg-red-700 transition-colors duration-300">
                            <i class="fas fa-trash-alt mr-2"></i> Remove
                        </button>
                    </form>
                </div>
            </div>

            <!-- User Information Section -->
            <div class="space-y-4 border-t border-gray-700 pt-6">
                <div class="flex items-center space-x-4">
                    <i class="fa-solid fa-user text-blue-500 text-2xl"></i>
                    <div>
                        <p class="text-gray-400">Username</p>
                        <p class="text-white text-xl font-semibold"><?php echo htmlspecialchars($user_info['username']); ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <i class="fa-solid fa-envelope text-blue-500 text-2xl"></i>
                    <div>
                        <p class="text-gray-400">Email</p>
                        <p class="text-white text-xl font-semibold"><?php echo htmlspecialchars($user_info['email']); ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <i class="fa-solid fa-user-tag text-blue-500 text-2xl"></i>
                    <div>
                        <p class="text-gray-400">Role</p>
                        <p class="text-white text-xl font-semibold"><?php echo htmlspecialchars(ucfirst($user_info['role'])); ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <i class="fa-solid fa-calendar-alt text-blue-500 text-2xl"></i>
                    <div>
                        <p class="text-gray-400">Member Since</p>
                        <p class="text-white text-xl font-semibold"><?php echo date('F j, Y, g:i a', strtotime($user_info['created_at'])); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- DELETE ACCOUNT BUTTON -->
            <div class="pt-6 border-t border-gray-700">
                <button id="delete-account-button" class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-lg font-medium rounded-lg text-white bg-red-700 hover:bg-red-800 transition-colors duration-300 shadow-md">
               Delete Account
                </button>
            </div>
            <!-- END DELETE ACCOUNT BUTTON -->

        </div>
    <?php else: ?>
        <p class="text-center text-red-400">Could not find user information.</p>
    <?php endif; ?>
</div>

<!-- Cropper Modal for image selection and cropping -->
<div id="cropper-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-gray-800 p-6 rounded-lg shadow-xl w-full max-w-lg mx-auto">
        <h2 class="text-xl font-bold text-white mb-4">Adjust Profile Picture</h2>
        <div class="w-full h-80 overflow-hidden mb-4">
            <img id="image-to-crop" src="" alt="Image to Crop" class="max-w-full h-auto">
        </div>
        <div class="flex justify-end gap-3 mt-4">
            <button id="cancel-crop-btn" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">Cancel</button>
            <button id="crop-and-upload-btn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">Crop & Upload</button>
        </div>
    </div>
</div>

<!-- Reusable Confirmation Modal -->
<div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-gray-800 p-6 rounded-lg shadow-xl w-full max-w-sm mx-auto">
        <h2 id="modal-title" class="text-xl font-bold text-white mb-4"></h2>
        <p id="modal-message" class="text-gray-300 mb-6"></p>
        <div class="flex justify-end gap-3">
            <button id="modal-cancel-btn" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">Cancel</button>
            <button id="modal-confirm-btn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"></button>
        </div>
    </div>
</div>

<!-- Modal for image preview -->
<div id="image-preview-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="relative p-4 rounded-lg shadow-xl max-w-2xl max-h-full">
        <button id="close-preview-btn" class="absolute top-2 right-2 text-white bg-gray-800 rounded-full p-2 hover:bg-gray-700 transition-colors duration-300 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <img id="modal-image" src="" alt="Profile Picture Preview" class="max-w-full max-h-screen object-contain rounded-lg">
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const profilePictureContainer = document.getElementById('profile-picture-container');
        let profilePicture = document.getElementById('profile-picture');
        let defaultProfileIcon = document.getElementById('default-profile-icon');
        const imagePreviewModal = document.getElementById('image-preview-modal');
        const modalImage = document.getElementById('modal-image');
        const closePreviewBtn = document.getElementById('close-preview-btn');
        let previewOverlay = document.querySelector('#profile-picture-container .absolute');
        const removeButtonContainer = document.getElementById('remove-form');

        const cropperModal = document.getElementById('cropper-modal');
        const imageToCrop = document.getElementById('image-to-crop');
        const cancelCropBtn = document.getElementById('cancel-crop-btn');
        const cropAndUploadBtn = document.getElementById('crop-and-upload-btn');
        let cropper;

        // Reusable modal elements
        const confirmModal = document.getElementById('confirm-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalMessage = document.getElementById('modal-message');
        const profilePictureInput = document.getElementById('profile_picture_input');
        const removeButton = document.getElementById('remove-button');
        const removeForm = document.getElementById('remove-form');
        
        // --- NEW ELEMENT ---
        const deleteAccountButton = document.getElementById('delete-account-button');


        // Show image preview modal
        const showImagePreview = () => {
            if (profilePicture) {
                modalImage.src = profilePicture.src;
                imagePreviewModal.classList.remove('hidden');
            }
        };

        // This listener is now always attached, which fixes the bug.
        profilePictureContainer.addEventListener('click', showImagePreview);

        // Hide image preview modal
        closePreviewBtn.addEventListener('click', () => {
             imagePreviewModal.classList.add('hidden');
        });

        imagePreviewModal.addEventListener('click', (event) => {
            if (event.target === imagePreviewModal) {
                imagePreviewModal.classList.add('hidden');
            }
        });

        // Handle file selection for cropping
        profilePictureInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const file = this.files[0];
                const reader = new FileReader();
                reader.onload = function(event) {
                    imageToCrop.src = event.target.result;
                    cropperModal.classList.remove('hidden');

                    // Initialize Cropper.js
                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper(imageToCrop, {
                        aspectRatio: 1, // Square crop box
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        background: false,
                        zoomable: true,
                        movable: true,
                    });
                };
                reader.readAsDataURL(file);
            }
        });

        // Function to update the UI after a successful image upload
        const updateUIForImageUpload = (newImagePath) => {
            // Check if profilePicture element exists
            if (!profilePicture) {
                profilePicture = document.createElement('img');
                profilePicture.id = 'profile-picture';
                profilePicture.alt = 'Profile Picture';
                profilePicture.classList.add('w-full', 'h-full', 'object-cover', 'transition-transform', 'duration-300', 'transform', 'group-hover:scale-110');
                profilePictureContainer.prepend(profilePicture);

                // Remove the default icon if it's there
                if (defaultProfileIcon) {
                    defaultProfileIcon.remove();
                    defaultProfileIcon = null;
                }
            }

            // Update the image source and display it
            profilePicture.src = newImagePath + '?' + new Date().getTime();
            profilePicture.style.opacity = '1';

            // Ensure the container's background class is removed
            profilePictureContainer.classList.remove('bg-gray-700');

            // Update the overlay text to "Click to Preview"
            if (previewOverlay) {
                previewOverlay.innerHTML = '<span class="text-white text-sm font-semibold">Click to Preview</span>';
            }

            // Show the remove button
            if (removeButtonContainer) {
                removeButtonContainer.classList.remove('hidden');
            }
        };


        // Handle crop and upload button click
        cropAndUploadBtn.addEventListener('click', async () => {
            const croppedCanvas = cropper.getCroppedCanvas({
                width: 256,
                height: 256,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            // Show a temporary loading state
            profilePictureContainer.style.backgroundImage = 'url("data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzgiIGhlaWdodD0iMzgiIHZpZXdCb3g9IjAgMCAzOCAzOCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZzIgZmlsbD0iI2ZmZmZmZiIgZmlsbC1vcGFjaXR5PSIuOCI+PHBhdGggZD0iTTMwLjI1IDE4LjcwN2EzLjU2NyAzLjU2NyAwIDAgMC0zLjU2Ny0zLjU2N0wxOSA2LjI1YTMuNTY3IDMuNTY3IDAgMCAwLTMuNTY3IDMuNTY3TDMuNTA3IDE4LjcwN2EzLjU2NyAzLjU2NyAwIDAgMCAzLjU2NyAzLjU2N0wxOSA5LjczYTMuNTY3IDMuNTY3IDAgMCAwLTMuNTY3LTMuNTY3TDMwLjI1IDE4LjcwN3oiLz48L2c+PC9zdmc+")';
            profilePictureContainer.style.backgroundRepeat = 'no-repeat';
            profilePictureContainer.style.backgroundPosition = 'center';
            if (profilePicture) {
                profilePicture.style.opacity = '0';
            }
            
            // Convert canvas to a blob and then upload
            croppedCanvas.toBlob(async (blob) => {
                try {
                    const formData = new FormData();
                    formData.append('profile_picture', blob, 'profile.jpg');
                    
                    const response = await fetch('upload_profile_picture.php', {
                        method: 'POST',
                        body: formData,
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        console.log('Upload successful:', result);
                        updateUIForImageUpload(result.image_path);

                    } else {
                        console.error('Upload failed:', result);
                        // Reset to previous state or show error
                    }
                } catch (error) {
                    console.error('Error during upload:', error);
                } finally {
                    profilePictureContainer.style.backgroundImage = '';
                }

            }, 'image/jpeg', 0.8);

            // Hide the modal
            cropperModal.classList.add('hidden');
        });

        // Cancel crop
        cancelCropBtn.addEventListener('click', () => {
            cropperModal.classList.add('hidden');
            profilePictureInput.value = '';
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        });

        // Function to set up the Confirmation Modal for an action (Generalized and Reusable)
        const setupConfirmationModal = (title, message, confirmText, confirmAction, isDestructive = false) => {
            const modalConfirmBtn = document.getElementById('modal-confirm-btn');
            const modalCancelBtn = document.getElementById('modal-cancel-btn');

            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modalConfirmBtn.textContent = confirmText;
            
            // Set styles for destructive action
            if (isDestructive) {
                modalConfirmBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                modalConfirmBtn.classList.add('bg-red-600', 'hover:bg-red-700');
            } else {
                modalConfirmBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                modalConfirmBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
            }

            // Clean up previous event listeners (Crucial for reusable modal)
            const newConfirmBtn = modalConfirmBtn.cloneNode(true);
            modalConfirmBtn.parentNode.replaceChild(newConfirmBtn, modalConfirmBtn);
            const newCancelBtn = modalCancelBtn.cloneNode(true);
            modalCancelBtn.parentNode.replaceChild(newCancelBtn, modalCancelBtn);

            const finalConfirmBtn = document.getElementById('modal-confirm-btn');
            const finalCancelBtn = document.getElementById('modal-cancel-btn');

            finalConfirmBtn.addEventListener('click', () => {
                confirmAction();
                confirmModal.classList.add('hidden');
            });

            finalCancelBtn.addEventListener('click', () => {
                confirmModal.classList.add('hidden');
            });

            confirmModal.classList.remove('hidden');
        };


        // Handle remove button click for confirmation
        if (removeButton) {
            removeButton.addEventListener('click', (event) => {
                setupConfirmationModal(
                    'Confirm Removal', 
                    'Are you sure you want to remove your profile picture? This cannot be undone.', 
                    'Remove Picture', 
                    () => removeForm.submit(), // Action: Submit the form to delete the picture
                    true // isDestructive
                );
            });
        }
        
        // --- NEW DELETE ACCOUNT LOGIC ---
        if (deleteAccountButton) {
            deleteAccountButton.addEventListener('click', () => {
                setupConfirmationModal(
                    'PERMANENT ACCOUNT DELETION', 
                    'WARNING: This action is irreversible. All your data, subscriptions, and settings will be permanently removed. Do you wish to proceed?', 
                    'DELETE ACCOUNT', 
                    () => {
                        // Action: Redirect to the dedicated deletion script
                        window.location.href = 'delete_account.php'; 
                    },
                    true // isDestructive (uses red button)
                );
            });
        }
        // --- END NEW DELETE ACCOUNT LOGIC ---

    });
</script>

<?php require_once 'includes/footer.php'; ?>