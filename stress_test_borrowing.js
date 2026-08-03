import http from "k6/http";
import { sleep } from "k6";

// Random stress-test students
const students = [
  "Stress Test Student 1",
  "Stress Test Student 2",
  "Stress Test Student 3",
  "Stress Test Student 4",
  "Stress Test Student 5",
  "Stress Test Student 6",
  "Stress Test Student 7",
  "Stress Test Student 8",
  "Stress Test Student 9",
  "Stress Test Student 10",
];

// Random stress-test books
const books = [
  "Stress Testing Book 1",
  "Stress Testing Book 2",
  "Stress Testing Book 3",
  "Stress Testing Book 4",
  "Stress Testing Book 5",
  "Stress Testing Book 6",
  "Stress Testing Book 7",
  "Stress Testing Book 8",
  "Stress Testing Book 9",
  "Stress Testing Book 10",
  "Stress Testing Book 11",
  "Stress Testing Book 12",
  "Stress Testing Book 13",
  "Stress Testing Book 14",
  "Stress Testing Book 15",
  "Stress Testing Book 16",
  "Stress Testing Book 17",
  "Stress Testing Book 18",
  "Stress Testing Book 19",
  "Stress Testing Book 20",
];

export const options = {
  vus: 5,
  duration: "1m30s",
};

export default function () {
  const student = students[Math.floor(Math.random() * students.length)];

  const book = books[Math.floor(Math.random() * books.length)];

  const payload = {
    namaCari: student,
    judulCari: book,
  };

  const res = http.post(
    "https://project-perpustakaan-sql-402234429231.us-central1.run.app/peminjaman-perpustakaan/add",
    payload,
  );

  console.log(`User: ${student} | Book: ${book} | Status: ${res.status}`);

  sleep(1);
}
