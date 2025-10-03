

<?php $__env->startSection('content'); ?>

    <div class="container-fluid">

        <form action="" method="post" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
         

            <!-- Hidden ID field -->
            <input type="hidden" name="id" value="<?php echo e($news->id); ?>">

            <input style="width:300px;" type="text" placeholder="Title" name="title" value="<?php echo e(old('title', $news->title)); ?>" required class="form-control">

            <!-- Replaced input with textarea for larger text fields -->
            <textarea style="width:80%; height:100px;" placeholder="About" name="about" required class=""><?php echo e(old('about', $news->about)); ?></textarea>
            <textarea style="width:80%; height:300px;" placeholder="Info" name="info" required class=""><?php echo e(old('info', $news->info)); ?></textarea>

            <br>Picture <br>
            <input style="width:300px;" type="file" name="img" class="form-control"><br>
            <?php if($news->img): ?>
                <img src="<?php echo e(asset($news->img)); ?>" alt="Current Image" style="width: 100px; height: 100px; border-radius: 50%;"><br>
            <?php endif; ?>
            <p class="help-block text-danger"></p>

            <!-- YouTube input with embedded preview -->
            <input style="width:100%;" type="text" placeholder="YouTube Link" name="youtube" value="<?php echo e(old('youtube', $news->youtube)); ?>" class="form-control">

            <?php if($news->youtube): ?>
                <?php
                    // Extract video ID from YouTube URL
                    parse_str(parse_url($news->youtube, PHP_URL_QUERY), $youtubeParams);
                    $youtubeId = $youtubeParams['v'] ?? null;
                ?>

                <?php if($youtubeId): ?>
                    <br>
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo e($youtubeId); ?>" frameborder="0" allowfullscreen></iframe>
                <?php endif; ?>
            <?php endif; ?>

            <br>

            <select name="cat_id" class="form-control" required style="width: 24%;">
                <option value="">Job Category</option>
                <?php $__currentLoopData = $cat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($item->id); ?>" <?php echo e($news->cat_id == $item->id ? 'selected' : ''); ?>>
                        <?php echo e($item->title); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <input type="submit" value="Update" class="btn btn-default"><br>
        </form>

    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make("admin.main", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/brightbr/public_html/resources/views/admin/pages/editnews.blade.php ENDPATH**/ ?>