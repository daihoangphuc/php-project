<nav class="bg-white border-b border-gray-200 px-4 py-2.5 dark:bg-gray-800 dark:border-gray-700 fixed left-0 right-0 top-0 z-50">
    <div class="flex flex-wrap justify-between items-center">
        <div class="flex justify-start items-center">
            <a href="index.php" class="flex items-center">
                <span class="self-center text-2xl font-semibold whitespace-nowrap text-white">HSTV Admin</span>
            </a>
        </div>
        <div class="flex items-center lg:order-2">
            <button type="button" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700">
                <span class="mr-2"><?php echo $_SESSION['user']['HoTen']; ?></span>
            </button>
            <a href="index.php?module=auth&action=logout" 
               class="text-white bg-red-600 hover:bg-red-700 font-medium rounded-lg text-sm px-4 py-2 text-center">
                Đăng xuất
            </a>
        </div>
    </div>
</nav> 