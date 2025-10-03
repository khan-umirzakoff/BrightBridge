

<?php $__env->startSection("content"); ?>
    <div class="bradcam_area bradcam_bg_1">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="bradcam_text">
                        <h3>Add New Job</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ bradcam_area -->

    <!-- job_add_area_start -->
    <div class="job_add_area job_page_padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-8 mx-auto">
                    <div class="contact-form">
                        <!-- Display Success Messages -->
                        <?php if(session('success')): ?>
                            <div class="alert alert-success">
                                <?php echo e(session('success')); ?>

                            </div>
                        <?php endif; ?>

                        <!-- Display Error Messages -->
                        <?php if(session('error')): ?>
                            <div class="alert alert-danger">
                                <?php echo e(session('error')); ?>

                            </div>
                        <?php endif; ?>

                        <!-- Add Job Form -->
                        <form action="" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                                <p class="help-block text-danger"></p>

                            <div class="control-group">
                                <input type="text" class="form-control" name="title" placeholder="Job Title"
                                    required="required" data-validation-required-message="Please enter the Job Title" />
                                <p class="help-block text-danger"></p>
                            </div>

                            <!-- Job Location - Dropdown -->
                          <div class="single_field">
        <select id="location" class="wide" name="location">
            <option value="">Select Location</option>
            <?php $__currentLoopData = [
                'Uzbekistan, Tashkent', 'Uzbekistan, Samarkand', 'Uzbekistan, Bukhara',
                'Uzbekistan, Khiva', 'Uzbekistan, Fergana', 'Uzbekistan, Namangan',
                'Uzbekistan, Andijan', 'Uzbekistan, Nukus', 'Uzbekistan, Jizzakh',
                'Uzbekistan, Navoi', 'Uzbekistan, Termez', 'Uzbekistan, Karshi',
                'Uzbekistan, Gulistan', 'Uzbekistan, Angren'
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($city); ?>"><?php echo e($city); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div><br> <br>


                            <div class="single_field">
        <select id="location" class="wide" name="type">
            <option value="">Select Type</option>
            <?php $__currentLoopData = [
                'Full-time', 'Part-time', 'Internship',
                'Freelance', 'Volunteering','Full-time and Part-time']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($type); ?>"><?php echo e($type); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div><br> <br>

                            <div class="control-group">
                                <textarea style="height: 200px;" class="form-control" name="info" placeholder="Job Description"
                                    required="required" data-validation-required-message="Please enter the Job Description"></textarea>
                                <p class="help-block text-danger"></p>
                            </div>

                            <div class="control-group">
                                <input type="text" class="form-control" name="responses" placeholder="Job Responsibilities"
                                    required="required" data-validation-required-message="Please enter Job Responsibilities" />
                                <p class="help-block text-danger"></p>
                            </div>

                            <!-- Changed to Textarea -->
                            <div class="control-group">
                                <textarea style="height: 200px;" class="form-control" name="quals" placeholder="Required Qualifications"
                                    required="required" data-validation-required-message="Please enter the Qualifications"></textarea>
                                <p class="help-block text-danger"></p>
                            </div>

                            <!-- Changed to Textarea -->
                            <div class="control-group">
                                <textarea style="height: 200px;" class="form-control" name="benefits" placeholder="Job Benefits"
                                    required="required" data-validation-required-message="Please enter Job Benefits"></textarea>
                                <p class="help-block text-danger"></p>
                            </div>

           <!-- Salary Type Selection (Radio Buttons) -->
<div class="single-field">
    <label>Salary Type</label><br>
    <input type="radio" id="fixed_salary" name="salary_option" value="fixed" required>
    <label for="fixed_salary">Fixed Salary</label>

    <input type="radio" id="negotiable_salary" name="salary_option" value="negotiable" required>
    <label for="negotiable_salary">Negotiable</label>
</div>
<br>

<!-- Salary Input (Initially Hidden) -->
<div id="salary_input_div" class="control-group" style="display: none;">
    <label for="salary_input">Enter Salary</label>
    <input type="text" class="form-control" id="salary_input" name="salary" placeholder="Enter Salary" />
    <p class="help-block text-danger"></p>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let fixedSalaryRadio = document.getElementById("fixed_salary");
    let negotiableSalaryRadio = document.getElementById("negotiable_salary");
    let salaryInputDiv = document.getElementById("salary_input_div");
    let salaryInput = document.getElementById("salary_input");

    // Show input when "Fixed Salary" is selected
    fixedSalaryRadio.addEventListener("change", function () {
        salaryInputDiv.style.display = "block"; 
        salaryInput.setAttribute("required", "required"); 
    });

    // Hide input when "Negotiable" is selected
    negotiableSalaryRadio.addEventListener("change", function () {
        salaryInputDiv.style.display = "none"; 
        salaryInput.removeAttribute("required"); 
        salaryInput.value = ""; 
    });

    // Format salary input (adds spaces after every 3 digits)
    salaryInput.addEventListener("input", function () {
        let value = this.value.replace(/\D/g, ""); // Remove non-numeric characters
        this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, " "); // Add spaces every 3 digits
    });
});
</script>


                            <!-- Job Category Dropdown -->
                           
                           
                            <div class="single-field">
                                <select name="cat_id" class="wide" required>
                                    <option value="">Job Category</option>
                                    <?php $__currentLoopData = $category; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($item->id); ?>"><?php echo e($item->title); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <p class="help-block text-danger"></p>
                            </div>
                            
                            

                            <div><br><br>
                                <button class="btn btn-primary py-2 px-4" type="submit">Submit Job</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- job_add_area_end -->

    <!-- JavaScript to Convert Enter Key to <br> in Textareas -->
    <script>
document.addEventListener("DOMContentLoaded", function () {
    let textareas = document.querySelectorAll("textarea");

    textareas.forEach(function (textarea) {
        textarea.addEventListener("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault(); // Prevent default behavior
                let cursorPos = this.selectionStart;
                let textBefore = this.value.substring(0, cursorPos);
                let textAfter = this.value.substring(cursorPos);
                this.value = textBefore + "\n" + textAfter; // Insert a real new line
                this.selectionStart = this.selectionEnd = cursorPos + 1; // Move cursor after new line
            }
        });
    });
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make("main2.main", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/brightbr/public_html/resources/views/pages/addjob.blade.php ENDPATH**/ ?>