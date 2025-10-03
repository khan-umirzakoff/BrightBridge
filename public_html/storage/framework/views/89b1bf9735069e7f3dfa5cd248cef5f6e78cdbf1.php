

<?php $__env->startSection("content"); ?>
    <div class="bradcam_area bradcam_bg_1">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="bradcam_text">
                        <h3>Edit Candidate Profile</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid pt-5">
        <div class="text-center mb-4">
            <h2 class="section-title px-5"><span class="px-2">Edit Candidate Profile</span></h2>
        </div>
        <div class="row px-xl-5 pb-3">
            <div class="contact-form mx-auto" style="width: 50%; background-color: #f8f9fa; padding: 20px; border-radius: 10px;">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>" />

                    <!-- First Name -->
                    <div class="form-group mb-3">
                        <label for="first_name" class="font-weight-bold">First Name</label>
                        <input type="text" class="form-control" name="first_name" value="<?php echo e($candidate->first_name ?? ''); ?>" required>
                    </div>

                    <!-- Last Name -->
                    <div class="form-group mb-3">
                        <label for="last_name" class="font-weight-bold">Last Name</label>
                        <input type="text" class="form-control" name="last_name" value="<?php echo e($candidate->last_name ?? ''); ?>" required>
                    </div>

                    <!-- Age -->
                    <div class="form-group mb-3">
                        <label for="age" class="font-weight-bold">Age</label>
                        <input type="number" class="form-control" name="age" value="<?php echo e($candidate->age ?? ''); ?>">
                    </div>

                    <!-- Email -->
                    <div class="form-group mb-3">
                        <label for="email" class="font-weight-bold">Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo e($candidate->email ?? ''); ?>" required>
                    </div>

                    <!-- Phone -->
                    <div class="form-group mb-3">
                        <label for="phone" class="font-weight-bold">Phone</label>
                        <input type="text" class="form-control" name="phone" value="<?php echo e($candidate->phone ?? ''); ?>">
                    </div>

                    <!-- Job Position -->
                    <div class="form-group mb-3">
                        <label for="job_position" class="font-weight-bold">Job Position</label>
                        <input type="text" class="form-control" name="job_position" value="<?php echo e($candidate->job_position ?? ''); ?>">
                    </div>

                    <!-- Skills -->
                    <div class="form-group mb-3">
                        <label for="skills" class="font-weight-bold">Skills</label>
                        <textarea class="form-control" name="skills" rows="3" placeholder="E.g., teamwork, communication, programming"><?php echo e($candidate->skills ?? ''); ?></textarea>
                    </div>

                    <!-- Experience -->
                    <div class="form-group mb-3">
                        <label for="experience" class="font-weight-bold">Experience (Years)</label>
                        <input type="number" class="form-control" name="experience" value="<?php echo e($candidate->experience_years ?? ''); ?>">
                    </div>

                    <!-- Address -->
                    <div class="form-group mb-3">
                        <label for="address" class="font-weight-bold">Address</label>
                        <input type="text" class="form-control" name="address" value="<?php echo e($candidate->address ?? ''); ?>">
                    </div>

                    <!-- Expected Salary -->
                    <div class="form-group mb-3">
                        <label for="salary" class="font-weight-bold">Expected Salary</label>
                        <input type="number" class="form-control" name="salary" value="<?php echo e($candidate->expected_salary ?? ''); ?>">
                    </div>

                    <!-- Resume Upload -->
                    <div class="form-group mb-3">
                        <label for="resume" class="font-weight-bold">Upload Resume</label>
                        <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx">
                        <?php if(!empty($candidate->resume)): ?>
                            <small class="form-text text-muted">Current: <a href="<?php echo e(asset($candidate->resume)); ?>" target="_blank">View Resume</a></small>
                        <?php endif; ?>
                    </div>

                    <!-- Profile Image Upload -->
                    <div class="form-group mb-4">
                        <label for="img" class="font-weight-bold">Profile Image</label>
                        <input type="file" class="form-control" name="img" accept=".jpg,.jpeg,.png">
                        <?php if(!empty($candidate->img)): ?>
                            <small class="form-text text-muted">Current: <img src="<?php echo e(asset($candidate->img)); ?>" alt="Profile Image" style="width: 50px; height: 50px; border-radius: 50%;"></small>
                        <?php endif; ?>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button class="btn btn-primary py-2 px-4" type="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Edit Profile End -->
<?php $__env->stopSection(); ?>

<?php echo $__env->make("main2.main2", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/brightbr/public_html/resources/views/pages/edit.blade.php ENDPATH**/ ?>