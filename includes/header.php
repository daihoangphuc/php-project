<nav class="bg-white border-gray-200 dark:bg-gray-900">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
        <a href="index.php" class="flex items-center space-x-3 rtl:space-x-reverse">
            <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">CLB HSTV</span>
        </a>
        <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
            <?php if (isset($_SESSION['user'])): ?>
                <button type="button" class="flex text-sm rounded-full md:me-0" id="user-menu-button">
                    <span class="sr-only">Open user menu</span>
                    <img class="w-8 h-8 rounded-full" src="<?php echo $_SESSION['user']['AnhDaiDien'] ?? 'assets/images/default-avatar.png'; ?>" alt="user photo">
                </button>
                <div class="hidden z-50 my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow dark:bg-gray-700 dark:divide-gray-600" id="user-dropdown">
                    <div class="px-4 py-3">
                        <span class="block text-sm text-gray-900 dark:text-white"><?php echo $_SESSION['user']['HoTen']; ?></span>
                        <span class="block text-sm text-gray-500 truncate dark:text-gray-400"><?php echo $_SESSION['user']['Email']; ?></span>
                    </div>
                    <ul class="py-2" aria-labelledby="user-menu-button">
                        <li>
                            <a href="index.php?module=users&action=profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Thông tin cá nhân</a>
                        </li>
                        <li>
                            <a href="index.php?module=auth&action=logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Đăng xuất</a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="index.php?module=auth&action=login" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2 text-center">Đăng nhập</a>
            <?php endif; ?>
        </div>
        <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-user">
            <ul class="flex flex-col font-medium p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
                <li>
                    <a href="index.php" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Trang chủ</a>
                </li>
                <li>
                    <a href="index.php?module=activities" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Hoạt động</a>
                </li>
                <li>
                    <a href="index.php?module=news" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Tin tức</a>
                </li>
            </ul>
        </div>
    </div>
</nav> 