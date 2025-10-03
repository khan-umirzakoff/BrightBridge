

<?php $__env->startSection('content'); ?>

    <div class="container-fluid">
        <form action="" method="post" enctype="multipart/form-data">

            <?php echo csrf_field(); ?>

            <!-- Title Input -->
            <input style="width:300px;" type="text" placeholder="Title" name="title" required class="form-control">

            <!-- About Input -->
            <input style="width:300px;" type="text" placeholder="About" name="about" required class="form-control">

            <!-- Info Textarea -->
            <textarea class="form-control" name="info" id="message" cols="30" rows="9" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter full information'" placeholder="Enter full information" style="width: 40%;"></textarea>

            <!-- Picture Input -->
            Picture <br>
            <input style="width:300px;" type="file" name="img" />

            <!-- Category Select -->
            <select name="cat_id" class="form-control" required class="form-control" style="width: 24%;">
                <option value="">Job Category</option>
                <?php $__currentLoopData = $cat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($item->id); ?>"><?php echo e($item->title); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <!-- Optional YouTube Link Input -->
            <input style="width:300px;" type="url" placeholder="YouTube Link (Optional)" name="youtube" class="form-control">

            <!-- Submit Button -->
            <input style="width:300px;" type="submit" class="button" value="Submit">

        </form>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make("admin.main", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/brightbr/public_html/resources/views/admin/pages/addnews.blade.php ENDPATH**/ ?>