const SUPABASE_CONFIG = {
  url: "https://vcqrsgwduwnuqqaflrca.supabase.co",
  anonKey:
    "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZjcXJzZ3dkdXdudXFxYWZscmNhIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjA4MDM1MjgsImV4cCI6MjA3NjM3OTUyOH0.0hu-pfbpr8KhyGngsL2Y4ExK4r45iT4uCpVZb0bdDXY",
};

if (
  typeof window !== "undefined" &&
  window.supabase &&
  typeof window.supabase.createClient === "function"
) {
  window.supabase_client = window.supabase.createClient(
    SUPABASE_CONFIG.url,
    SUPABASE_CONFIG.anonKey,
  );
} else {
  console.warn("⚠️ Supabase library not loaded yet");
  setTimeout(() => {
    if (window.supabase && typeof window.supabase.createClient === "function") {
      window.supabase_client = window.supabase.createClient(
        SUPABASE_CONFIG.url,
        SUPABASE_CONFIG.anonKey,
      );
    }
  }, 100);
}

class BookRealtimeSync {
  constructor() {
    this.channel = null;
    this.isConnected = false;
  }

  init() {
    if (!window.supabase_client) {
      setTimeout(() => this.init(), 100);
      return;
    }

    this.channel = window.supabase_client
      .channel("books-changes")
      .on(
        "postgres_changes",
        {
          event: "*",
          schema: "public",
          table: "books",
        },
        (payload) => this.handleBookChange(payload),
      )
      .subscribe((status) => {
        if (status === "SUBSCRIBED") {
          this.isConnected = true;
          this.showConnectionStatus("connected");
        } else if (status === "CLOSED") {
          this.isConnected = false;
          this.showConnectionStatus("disconnected");
        } else if (status === "CHANNEL_ERROR") {
          console.error("⚠️ Real-time connection error");
          this.showConnectionStatus("error");
        }
      });
  }

  handleBookChange(payload) {
    const { eventType, new: newRecord, old: oldRecord } = payload;

    switch (eventType) {
      case "INSERT":
        this.handleBookInsert(newRecord);
        break;
      case "UPDATE":
        this.handleBookUpdate(newRecord, oldRecord);
        break;
      case "DELETE":
        this.handleBookDelete(oldRecord);
        break;
    }
  }

  handleBookInsert(book) {
    this.showNotification(`📚 Buku baru ditambahkan: ${book.title}`, "success");

    if (typeof loadBooks === "function") {
      loadBooks(currentPage);
    } else {
      location.reload();
    }
  }

  handleBookUpdate(newBook, oldBook) {
    this.showNotification(`📝 Buku diperbarui: ${newBook.title}`, "info");

    this.updateBookCard(newBook);

    if (typeof loadBooks === "function") {
      loadBooks(currentPage);
    }
  }

  handleBookDelete(book) {
    this.showNotification(`🗑️ Buku dihapus: ${book.title}`, "warning");

    this.removeBookCard(book.id);

    if (typeof loadBooks === "function") {
      loadBooks(currentPage);
    }
  }

  updateBookCard(book) {
    const bookCard = document.querySelector(`[data-book-id="${book.id}"]`);
    if (bookCard) {
      const titleElement = bookCard.querySelector("h2");
      if (titleElement) titleElement.textContent = book.title;

      const imageElement = bookCard.querySelector("img");
      if (imageElement && book.image) imageElement.src = book.image;

      bookCard.classList.add("book-updated-animation");
      setTimeout(() => {
        bookCard.classList.remove("book-updated-animation");
      }, 1000);
    }
  }

  removeBookCard(bookId) {
    const bookCard = document.querySelector(`[data-book-id="${bookId}"]`);
    if (bookCard) {
      bookCard.style.transition = "opacity 0.3s ease";
      bookCard.style.opacity = "0";
      setTimeout(() => bookCard.remove(), 300);
    }
  }

  showConnectionStatus(status) {
    let indicator = document.getElementById("realtimeStatusIndicator");

    if (!indicator) {
      indicator = document.createElement("div");
      indicator.id = "realtimeStatusIndicator";
      indicator.style.cssText = `
        position: fixed;
        top: 10px;
        right: 10px;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        z-index: 9999;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      `;
      document.body.appendChild(indicator);
    }

    switch (status) {
      case "connected":
        indicator.style.background = "#d1e7dd";
        indicator.style.color = "#0f5132";
        indicator.innerHTML = "🟢 Live (Terhubung)";
        break;
      case "disconnected":
        indicator.style.background = "#f8d7da";
        indicator.style.color = "#842029";
        indicator.innerHTML = "🔴 Offline";
        break;
      case "error":
        indicator.style.background = "#fff3cd";
        indicator.style.color = "#664d03";
        indicator.innerHTML = "⚠️ Koneksi Bermasalah";
        break;
    }

    if (status === "connected") {
      setTimeout(() => {
        indicator.style.opacity = "0.5";
      }, 3000);
    }
  }

  showNotification(message, type = "info") {
    const toastContainer = document.getElementById("toastContainer");

    if (!toastContainer) {
      const container = document.createElement("div");
      container.id = "toastContainer";
      container.style.cssText = `
        position: fixed;
        top: 70px;
        right: 20px;
        z-index: 9999;
      `;
      document.body.appendChild(container);
    }

    const toastId = "toast-" + Date.now();
    const toastHTML = `
      <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-${this.getBootstrapColor(type)} text-white">
          <strong class="me-auto">📡 Update Real-time</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
          ${message}
        </div>
      </div>
    `;

    document
      .getElementById("toastContainer")
      .insertAdjacentHTML("beforeend", toastHTML);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
      autohide: true,
      delay: 4000,
    });
    toast.show();

    toastElement.addEventListener("hidden.bs.toast", () => {
      toastElement.remove();
    });
  }

  getBootstrapColor(type) {
    const colors = {
      success: "success",
      info: "info",
      warning: "warning",
      error: "danger",
    };
    return colors[type] || "primary";
  }

  disconnect() {
    if (this.channel) {
      window.supabase_client.removeChannel(this.channel);
      this.isConnected = false;
    }
  }

  reconnect() {
    this.disconnect();
    this.init();
  }
}

const bookSync = new BookRealtimeSync();

document.addEventListener("DOMContentLoaded", () => {
  bookSync.init();

  document.addEventListener("visibilitychange", () => {
    if (!document.hidden && !bookSync.isConnected) {
      bookSync.reconnect();
    }
  });
});

window.addEventListener("beforeunload", () => {
  bookSync.disconnect();
});
