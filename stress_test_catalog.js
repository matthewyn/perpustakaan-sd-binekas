import http from "k6/http";
import { sleep } from "k6";

export const options = {
  vus: 50, // concurrent users
  duration: "1m",
};

export default function () {
  const res = http.get(
    "https://project-perpustakaan-sql-402234429231.us-central1.run.app/books/filter?page=1",
  );

  sleep(1);
}
