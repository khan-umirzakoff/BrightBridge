

<?php $__env->startSection("content"); ?>
    <div class="bradcam_area bradcam_bg_1">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="bradcam_text">
                        <h3 style="float: left; color: #fff; font-weight: bold;">My Applications</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Listings Section (Full screen) -->
    <div class="job_listing_area plus_padding">
        <div class="container-fluid">  <!-- Using container-fluid for full width -->
            <div class="row">
                <div class="col-lg-12">  <!-- Full width for job listing -->
                    <div class="job_lists m-0">
                        <div class="row">
                            <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    // Get the corresponding application for this job
                                    $application = $apps->firstWhere('job_id', $item->id);
                                ?>
                                <div class=" " style="width: 80%; margin: auto;">
                                    <div class="single_jobs white-bg d-flex justify-content-between mb-3 p-3 rounded shadow-sm">
                                        <div class="jobs_left d-flex align-items-center">
                                            <div class="thumb">
                                                <img src="<?php echo e($item->img); ?>" style="width: 50px;height: 50px;">
                                            </div>
                                            <div class="jobs_conetent ml-3">
                                                <a href="<?php echo e(route('job_details',['id'=>$item->id])); ?>"><h4 class="text-dark font-weight-bold"><?php echo e($item->title); ?></h4></a>
                                                <div class="links_locat d-flex align-items-center">
                                                    <div class="mr-3">
                                                        <p><i class="fa fa-briefcase"></i> <span class="text-muted"><?php echo e($item->company); ?></span></p>
                                                    </div>
                                                    <div class="mr-3">
                                                        <p><i class="fa fa-map-marker"></i> <span class="text-muted"><?php echo e($item->location); ?></span></p>
                                                    </div>
                                                    <div class="mr-3">
                                                        <p><i class="fa fa-clock-o"></i> <span class="text-muted"><?php echo e($item->type); ?></span></p>
                                                    </div>
                                                </div>
                                                <!-- Add the application date here -->
                                                
                                                    <p class="date">Applied on: <?php echo e(\Carbon\Carbon::parse($item->created_at)->format('d M Y')); ?></p>  <!-- Format as needed -->
                                               
                                            </div>
                                        </div>
                                        <div class="jobs_right d-flex align-items-center">
                                            <!-- See Candidates Button -->
                                            <div class="apply_now">
                                                <?php if($application): ?>
                                                    <a href="<?php echo e(route('view_candidates', ['job_id' => $item->id])); ?>" class="boxed-btn3">See Candidates</a><br>
                                                <?php else: ?>
                                                    <p>No candidates yet</p>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Status placed here, next to button -->
                                          <p class="status ml-3">Status: 
    <span class="
        <?php if($item->status == 1): ?>
            text-success  <!-- For 'active', set the class to 'text-success' -->
        <?php elseif($item->status == 2): ?>
            text-danger   <!-- For 'declined', set the class to 'text-danger' -->
        <?php elseif($item->status == 0): ?>
            no-status     <!-- No color for 'pending' status -->
        <?php endif; ?>
    ">
        <?php if($item->status == 0): ?>
            Pending
        <?php elseif($item->status == 1): ?>
            Active
        <?php elseif($item->status == 2): ?>
            Declined
        <?php endif; ?>
    </span>
</p>
  <p class="status ml-3">Status: 
    <span>  <a href="<?php echo e(route('jobedit2',['id'=>$item->id])); ?>" class="btn btn-warning">Edit</a>
                                        <a href="<?php echo e(route('jobdelete2',['id'=>$item->id])); ?>" class="btn btn-danger">Delete</a></span>   
</p>
                                        <!-- Edit and Delete buttons in the same row -->
                                      
                                

                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <!-- Pagination Controls (if any) -->
                        <!-- Optionally add pagination here if necessary -->
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<style>
/* General Styles */
.status {
    font-size: 0.9rem;
    font-weight: 600;
}

.status .text-success {
    color: #28a745;
}

.status .text-danger {
    color: #dc3545;
}

/* Styling for the apply button and status */
.jobs_right {
    display: flex;
    align-items: center;
    justify-content: flex-end; /* Align items to the right */
    width: 50%;
}

.apply_now {
    margin-right: 10px; /* Space between button and status */
}

.ml-3 {
    margin-left: 15px; /* Add some spacing between the button and status text */
}

/* Existing styles... */
</style>

<?php echo $__env->make("main2.main2", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/brightbr/public_html/resources/views/pages/myapplications2.blade.php ENDPATH**/ ?>