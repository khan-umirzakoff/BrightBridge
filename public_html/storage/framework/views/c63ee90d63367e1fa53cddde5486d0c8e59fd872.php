

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
                    <button class="btn btn-default" style="margin-top: 5px;">
                        <a href="<?php echo e(route('addtrainings')); ?>"><font color="black">Add Trainings</font></a>
                    </button>
                    <p class="help-block text-danger"></p>

                    <!-- Table for company listing -->
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                
                                <th>Id</th>
                                <th>Title</th>
                                <th>YouTube</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $trainings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <tr>
                                   
                                    <td><?php echo e($item->id); ?></td>
                                    <td><?php echo e($item->title); ?></td>
                                   <td style="width: 300px;">
    <?php
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', 
                   $item->youtube, $matches);
        $youtubeID = $matches[1] ?? '';
    ?>
    <?php if($youtubeID): ?>
        <iframe width="100%" height="170" 
                src="https://www.youtube.com/embed/<?php echo e($youtubeID); ?>" 
                frameborder="0" 
                allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen>
        </iframe>
    <?php else: ?>
        <p>No video available</p>
    <?php endif; ?>
</td>

                                    <td>
                                        <!-- Edit and Delete buttons in the same row -->
                                        <a href="<?php echo e(route('edittrainings',['id'=>$item->id])); ?>" class="btn btn-warning">Edit</a>
                                        <a href="<?php echo e(route('delltrainings',['id'=>$item->id])); ?>" class="btn btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this training?');">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div><!--/category-tab-->

        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make("admin.main", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/brightbr/public_html/resources/views/admin/pages/admintrainings.blade.php ENDPATH**/ ?>