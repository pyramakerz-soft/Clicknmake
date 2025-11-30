<div id="sidebar" class="bg-[#17253E] min-h-[100vh] h-full border-r-[1.33px] border-[#2E3545] lg:block">
    <div class="py-5 mx-9">
        <div class="px-3 md:px-5 py-3 md:py-4 bg-[#2E3646] rounded-md flex justify-between items-center">
            <img src="<?php echo e(asset('images/Paragraph container.png')); ?>" class="w-2/3 md:w-[90%]" />
            <?php if(Auth::guard('student')->user()): ?>
                
            <?php endif; ?>
        </div>
    </div>

    <nav class="flex flex-col text-[#A5ACBA]">
        <p class="text-base md:text-lg font-semibold px-9 mb-4 uppercase">Main Menu</p>

        <?php $__currentLoopData = $menuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menuItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div
                class="text_Style text-lg md:text-xl font-semibold px-9 py-3 md:py-5 flex items-center space-x-4 cursor-pointer">
                <i class="<?php echo e($menuItem['icon']); ?>"></i>
                <a href="<?php echo e($menuItem['route']); ?>" class="no-underline text-light ml-3"><?php echo e($menuItem['label']); ?></a>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div
            class="text_Style text-lg md:text-xl font-semibold px-9 py-3 md:py-5 flex items-center space-x-4 cursor-pointer">
            <i class="fi fi-bs-sign-out-alt transform rotate-180"></i>
            <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
                <?php echo csrf_field(); ?>
            </form>

            <a href="#" class="no-underline text-light ml-3"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Sign Out
            </a>
        </div>
    </nav>
</div>
<?php /**PATH /home/u413666390/domains/pyramakerz-artifacts.com/public_html/LMS/lms_pyramakerz/resources/views/components/sidebar.blade.php ENDPATH**/ ?>