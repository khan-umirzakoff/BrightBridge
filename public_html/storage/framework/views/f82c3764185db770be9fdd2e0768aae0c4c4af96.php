

<?php $__env->startSection('content'); ?>

    <div class="container-fluid">
        <form action="" method="post" enctype="multipart/form-data">

            <?php echo csrf_field(); ?>

            <!-- Title Input -->
            <input style="width:300px;" type="text" placeholder="Title" name="title" required class="form-control">

            

            <!-- Optional YouTube Link Input -->
            <input style="width:300px;" type="url" placeholder="YouTube Link" name="youtube" class="form-control">

            <!-- Submit Button -->
            <input style="width:300px;" type="submit" class="button" value="Submit">

        </form>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make("admin.main", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/brightbr/public_html/resources/views/admin/pages/addtrainings.blade.php ENDPATH**/ ?>