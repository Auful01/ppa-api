(function () {
  if (navigator.platform.indexOf("Win") > -1) {
    window.scrollInstances = [];

    // Halaman yang dilewati PerfectScrollbar
    const skipPages = ['/accesspoint', '/inspeksi-komputer'];

    const currentPath = window.location.pathname.toLowerCase();
    if (skipPages.some(p => currentPath.includes(p))) {
      console.log('PerfectScrollbar disabled on this page:', currentPath);
      return;
    }

    const main = document.querySelector("main");
    if (main && !main.classList.contains("ps-initialized")) {
      const ps = new PerfectScrollbar(main, { 
        wheelPropagation: false, // 🔹 Pastikan scroll event tidak tembus keluar
        suppressScrollX: true     // 🔹 Nonaktifkan scroll horizontal
      });
      window.scrollInstances.push(ps);
      main.classList.add("ps-initialized");
    }

    window.addEventListener("resize", () => {
      window.scrollInstances.forEach(ps => ps.update());
    });
  }
})();
