<?php $__env->startSection("content"); ?>
    <div class="bradcam_area bradcam_bg_1">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="bradcam_text">
                        <h3>Edit Company Profile</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid pt-5">
        <div class="text-center mb-4">
            <h2 class="section-title px-5"><span class="px-2">Edit Company Profile</span></h2>
        </div>
        <div class="row px-xl-5 pb-3">
            <div class="contact-form mx-auto" style="width: 50%; background-color: #f8f9fa; padding: 20px; border-radius: 10px;">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>" />

                    <!-- First Name -->
                    <div class="form-group mb-3">
                        <label for="first_name" class="font-weight-bold">First Name</label>
                        <input type="text" class="form-control" name="first_name" value="<?php echo e($company->first_name ?? ''); ?>" required>
                    </div>

                    <!-- Second Name -->
                    <div class="form-group mb-3">
                        <label for="second_name" class="font-weight-bold">Second Name</label>
                        <input type="text" class="form-control" name="second_name" value="<?php echo e($company->second_name ?? ''); ?>" required>
                    </div>

                    <!-- Age -->
                    <div class="form-group mb-3">
                        <label for="age" class="font-weight-bold">Age</label>
                        <input type="number" class="form-control" name="age" value="<?php echo e($company->age ?? ''); ?>">
                    </div>

                    <!-- Phone -->
                    <div class="form-group mb-3">
                        <label for="phone" class="font-weight-bold">Phone</label>
                        <input type="text" class="form-control" name="phone" value="<?php echo e($company->phone ?? ''); ?>">
                    </div>

                    <!-- Job Position -->
                    <div class="form-group mb-3">
                        <label for="job_position" class="font-weight-bold">Job Position</label>
                        <input type="text" class="form-control" name="job_position" value="<?php echo e($company->job_position ?? ''); ?>">
                    </div>

                    <!-- Company Name -->
                    <div class="form-group mb-3">
                        <label for="company_name" class="font-weight-bold">Company Name</label>
                        <input type="text" class="form-control" name="company_name" value="<?php echo e($company->company_name ?? ''); ?>">
                    </div>

                    <!-- Description -->
                   <div class="form-group mb-3">
    <label for="description" class="font-weight-bold">Company Description</label>
    <textarea class="form-control" name="description" rows="4"><?php echo e($company->description ?? ''); ?></textarea>
</div>

                    <!-- Profile Image Upload -->
                    <div class="form-group mb-4">
                        <label for="img" class="font-weight-bold">Profile Image</label>
                        <input type="file" class="form-control" name="img" accept=".jpg,.jpeg,.png">
                        <?php if(!empty($company->img)): ?>
                            <small class="form-text text-muted">Current: 
                                <img src="<?php echo e(asset($company->img)); ?>" alt="Profile Image" style="width: 50px; height: 50px; border-radius: 50%;">
                            </small>
                        <?php endif; ?>
                    </div>

                    <!-- Certificate Upload -->
                    <div class="form-group mb-4">
                        <label for="certificate" class="font-weight-bold">Certificate</label>
                        <input type="file" class="form-control" name="certificate" accept=".pdf,.doc,.docx">
                        <?php if(!empty($company->file)): ?>
                            <small class="form-text text-muted">Current: 
                                <a href="<?php echo e(asset($company->file)); ?>" target="_blank" class="btn btn-primary btn-sm">View Certificate</a>
                            </small>
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

<?php echo $__env->make("main2.main2", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/brightbr/public_html/resources/views/pages/editcomp.blade.php ENDPATH**/ ?>