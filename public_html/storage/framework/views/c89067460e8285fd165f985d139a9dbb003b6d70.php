

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
                        <a href="<?php echo e(route('addnews')); ?>"><font color="black">Add News</font></a>
                    </button>
                    <p class="help-block text-danger"></p>

                    <!-- Table for company listing -->
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Img</th>
                                <th>Id</th>
                                <th>Title</th>
                                <th>About</th>
                                <th>Info</th>
                                <th>YouTube</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $news; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $category = \App\Newscategory::where(['id' => $item->cat_id])->first(); ?>

                                <tr>
                                    <td>
                                        <img style="width: 50px;height: 50px;border-radius: 50%;" src="../<?php echo e($item->img); ?>">
                                    </td>
                                    <td><?php echo e($item->id); ?></td>
                                    <td><?php echo e($item->title); ?></td>
                                    <td><?php echo e($item->about); ?></td>
                                    <td><?php echo e($item->info); ?></td>
                                    <td><?php echo e($item->youtube); ?></td>
                                    <td><?php echo e($category->title ?? 'No Category'); ?></td>
                                    <td>
                                        <!-- Edit and Delete buttons in the same row -->
                                        <a href="<?php echo e(route('editnews',['id'=>$item->id])); ?>" class="btn btn-warning">Edit</a>
                                        <a href="<?php echo e(route('dellnews',['id'=>$item->id])); ?>" class="btn btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this news?');">
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

<?php echo $__env->make("admin.main", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/brightbr/public_html/resources/views/admin/pages/adminnews.blade.php ENDPATH**/ ?>