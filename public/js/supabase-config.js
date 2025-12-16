// supabase-config.js
// Add this to your project (create new file in your js folder)

const SUPABASE_CONFIG = {
  url: "https://vcqrsgwduwnuqqaflrca.supabase.co",
  anonKey:
    "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZjcXJzZ3dkdXdudXFxYWZscmNhIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjA4MDM1MjgsImV4cCI6MjA3NjM3OTUyOH0.0hu-pfbpr8KhyGngsL2Y4ExK4r45iT4uCpVZb0bdDXY",
};

// Initialize Supabase client
const supabase = window.supabase.createClient(
  SUPABASE_CONFIG.url,
  SUPABASE_CONFIG.anonKey
);

// Real-time book synchronization manager
class BookRealtimeSync {
  constructor() {
    this.channel = null;
    this.isConnected = false;
  }

  // Initialize real-time subscription
  init() {
    console.log("🔌 Initializing Supabase real-time connection...");

    // Subscribe to books table changes
    this.channel = supabase
      .channel("books-changes")
      .on(
        "postgres_changes",
        {
          event: "*", // Listen to INSERT, UPDATE, DELETE
          schema: "public",
          table: "books",
        },
        (payload) => this.handleBookChange(payload)
      )
      .subscribe((status) => {
        if (status === "SUBSCRIBED") {
          this.isConnected = true;
          console.log("✅ Connected to real-time updates");
          this.showConnectionStatus("connected");
        } else if (status === "CLOSED") {
          this.isConnected = false;
          console.log("❌ Real-time connection closed");
          this.showConnectionStatus("disconnected");
        } else if (status === "CHANNEL_ERROR") {
          console.error("⚠️ Real-time connection error");
          this.showConnectionStatus("error");
        }
      });
  }

  // Handle incoming book changes
  handleBookChange(payload) {
    console.log("📡 Real-time update received:", payload);

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

  // Handle new book insertion
  handleBookInsert(book) {
    console.log("➕ New book added:", book.title);

    // Show toast notification
    this.showNotification(`📚 Buku baru ditambahkan: ${book.title}`, "success");

    // Reload the book list
    if (typeof loadBooks === "function") {
      loadBooks(currentPage);
    } else {
      // Fallback: reload the entire page
      location.reload();
    }
  }

  // Handle book update
  handleBookUpdate(newBook, oldBook) {
    console.log("✏️ Book updated:", newBook.title);

    this.showNotification(`📝 Buku diperbarui: ${newBook.title}`, "info");

    // Update the specific book card in the DOM
    this.updateBookCard(newBook);

    // Or reload if update fails
    if (typeof loadBooks === "function") {
      loadBooks(currentPage);
    }
  }

  // Handle book deletion
  handleBookDelete(book) {
    console.log("🗑️ Book deleted:", book.title);

    this.showNotification(`🗑️ Buku dihapus: ${book.title}`, "warning");

    // Remove the book card from DOM
    this.removeBookCard(book.id);

    // Or reload
    if (typeof loadBooks === "function") {
      loadBooks(currentPage);
    }
  }

  // Update book card in DOM
  updateBookCard(book) {
    const bookCard = document.querySelector(`[data-book-id="${book.id}"]`);
    if (bookCard) {
      // Update the card content
      const titleElement = bookCard.querySelector("h2");
      if (titleElement) titleElement.textContent = book.title;

      const imageElement = bookCard.querySelector("img");
      if (imageElement && book.image) imageElement.src = book.image;

      // Add visual feedback
      bookCard.classList.add("book-updated-animation");
      setTimeout(() => {
        bookCard.classList.remove("book-updated-animation");
      }, 1000);
    }
  }

  // Remove book card from DOM
  removeBookCard(bookId) {
    const bookCard = document.querySelector(`[data-book-id="${bookId}"]`);
    if (bookCard) {
      bookCard.style.transition = "opacity 0.3s ease";
      bookCard.style.opacity = "0";
      setTimeout(() => bookCard.remove(), 300);
    }
  }

  // Show connection status indicator
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

    // Auto-hide after 3 seconds if connected
    if (status === "connected") {
      setTimeout(() => {
        indicator.style.opacity = "0.5";
      }, 3000);
    }
  }

  // Show toast notification
  showNotification(message, type = "info") {
    // Check if Bootstrap toast is available
    const toastContainer = document.getElementById("toastContainer");

    if (!toastContainer) {
      // Create toast container
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

    // Remove from DOM after hidden
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

  // Disconnect from real-time
  disconnect() {
    if (this.channel) {
      supabase.removeChannel(this.channel);
      this.isConnected = false;
      console.log("👋 Disconnected from real-time updates");
    }
  }

  // Reconnect
  reconnect() {
    this.disconnect();
    this.init();
  }
}

// Initialize on page load
const bookSync = new BookRealtimeSync();

// Auto-connect when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  bookSync.init();

  // Reconnect on visibility change (when user returns to tab)
  document.addEventListener("visibilitychange", () => {
    if (!document.hidden && !bookSync.isConnected) {
      console.log("🔄 Reconnecting real-time sync...");
      bookSync.reconnect();
    }
  });
});

// Clean up on page unload
window.addEventListener("beforeunload", () => {
  bookSync.disconnect();
});
