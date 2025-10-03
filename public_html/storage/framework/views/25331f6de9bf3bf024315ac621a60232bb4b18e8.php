

<?php $__env->startSection('content'); ?>

    <div class="container-fluid">
        <div class="category-tab"><!--category-tab-->

            <div class="tab-content">
                 <!-- Success Message -->
                <?php if(session('success')): ?>
                    <div class="alert alert-success">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if(session('error')): ?>
                    <div class="alert alert-danger">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>

                <br>
                <div class="col-lg-12">
                   
                 

               <br>
                <div class="col-lg-12 table-responsive">
                    <!-- Table for job listing -->
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Img</th>
                                <th>Status</th>
                                <th>Promotion</th>
                                <th>Title</th>
                                <th>Company</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Info</th>
                                <th>Responses</th>
                                <th>Qualifications</th>
                                <th>Benefits</th>
                                <th>Salary</th>
                                <th>Posted Date</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                            <?php $category = \App\Category::where(['id' =>$job->cat_id])->first(); ?>
                                <tr>
                                    <td><img style="width: 50px;height: 50px;border-radius: 50%;" src="../<?php echo e($job->img); ?>"></td>
                                    <td>
                                        <?php if($job->status == 0): ?>
                                             <div class="candidate-actions">
                                                <form action="<?php echo e(route('approve-job', ['id' => $job->id])); ?>" method="POST" style="display: inline;">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                </form>
                                                <form action="<?php echo e(route('decline-job', ['id' => $job->id])); ?>" method="POST" style="display: inline;">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-danger btn-sm">Decline</button>
                                                </form>
                                            </div>
                                        <?php elseif($job->status == 1): ?>
                                            <span class="badge badge-success">Confirmed</span>
                                        <?php elseif($job->status == 2): ?>
                                            <span class="badge badge-danger">Declined</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- ✅ Promotion Field Added -->
                                    <td>
                                        <?php if($job->promotion == 1): ?>
                                            <span class="badge badge-warning">Promoted</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Common</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?php echo e($job->title); ?></td>
                                    <td><?php echo e($job->company); ?></td>
                                    <td><?php echo e($job->location); ?></td>
                                    <td><?php echo e($job->type); ?></td>

                                    <!-- ✅ "See More" / "See Less" for long texts -->
                                    <td>
                                        <div class="text-content">
                                            <span class="short-text"><?php echo e(Str::limit($job->info, 50)); ?></span>
                                            <span class="full-text d-none"><?php echo e($job->info); ?></span>
                                            <?php if(strlen($job->info) > 50): ?>
                                                <button class="see-more-btn btn btn-link p-0">See More</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="text-content">
                                            <span class="short-text"><?php echo e(Str::limit($job->responses, 50)); ?></span>
                                            <span class="full-text d-none"><?php echo e($job->responses); ?></span>
                                            <?php if(strlen($job->responses) > 50): ?>
                                                <button class="see-more-btn btn btn-link p-0">See More</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="text-content">
                                            <span class="short-text"><?php echo e(Str::limit($job->quals, 50)); ?></span>
                                            <span class="full-text d-none"><?php echo e($job->quals); ?></span>
                                            <?php if(strlen($job->quals) > 50): ?>
                                                <button class="see-more-btn btn btn-link p-0">See More</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="text-content">
                                            <span class="short-text"><?php echo e(Str::limit($job->benefits, 50)); ?></span>
                                            <span class="full-text d-none"><?php echo e($job->benefits); ?></span>
                                            <?php if(strlen($job->benefits) > 50): ?>
                                                <button class="see-more-btn btn btn-link p-0">See More</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td><?php echo e($job->salary); ?></td>
                                    <td><?php echo e($job->date); ?></td>
                                    <td><?php echo e($category->title); ?></td>

                                    <td>
                                        <!-- Edit and Delete buttons in the same row -->
                                        <a href="<?php echo e(route('jobedit',['id'=>$job->id])); ?>" class="btn btn-warning">Edit</a>
                                        <a href="<?php echo e(route('jobdelete',['id'=>$job->id])); ?>" class="btn btn-danger">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div><!--/category-tab-->

        </div>
    </div>

    <!-- ✅ JavaScript for See More / See Less functionality -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".see-more-btn").forEach(button => {
                button.addEventListener("click", function() {
                    let parent = this.parentElement;
                    let shortText = parent.querySelector(".short-text");
                    let fullText = parent.querySelector(".full-text");

                    if (fullText.classList.contains("d-none")) {
                        fullText.classList.remove("d-none");
                        shortText.classList.add("d-none");
                        this.textContent = "See Less";
                    } else {
                        fullText.classList.add("d-none");
                        shortText.classList.remove("d-none");
                        this.textContent = "See More";
                    }
                });
            });
        });
    </script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make("admin.main", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/brightbr/public_html/resources/views/admin/pages/adminjobs.blade.php ENDPATH**/ ?>