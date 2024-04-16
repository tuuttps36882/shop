var index = 0; // Chỉ số của hình ảnh hiện tại
changeImage = function() {
    var imgs = ["IMG/banner/1.png", "IMG/banner/2.png", "IMG/banner/3.png", "IMG/banner/4.png"];
    document.getElementById('img').src = imgs[index];
    index++;
    if (index >= imgs.length) {
        index = 0; // Đặt lại chỉ số nếu đã đến hình ảnh cuối cùng trong mảng
    }
};
setInterval(changeImage, 1500); // Thực hiện hàm changeImage mỗi 1.5 giây
