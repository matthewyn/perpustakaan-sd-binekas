import http from "k6/http";
import { sleep } from "k6";

// PRE-GENERATED ACTIVE LOANS
const activeLoans = [
  {
    loanId: 8440,
    userId: 900003,
    bookId: 800007,
  },
  {
    loanId: 8441,
    userId: 900005,
    bookId: 800002,
  },
  {
    loanId: 8442,
    userId: 900004,
    bookId: 800008,
  },
  {
    loanId: 8443,
    userId: 900002,
    bookId: 800005,
  },
  {
    loanId: 8444,
    userId: 900007,
    bookId: 800009,
  },
  {
    loanId: 8445,
    userId: 900007,
    bookId: 800009,
  },
  {
    loanId: 8446,
    userId: 900003,
    bookId: 800010,
  },
  {
    loanId: 8447,
    userId: 900001,
    bookId: 800008,
  },
  {
    loanId: 8448,
    userId: 900008,
    bookId: 800005,
  },
  {
    loanId: 8449,
    userId: 900001,
    bookId: 800001,
  },
  {
    loanId: 8450,
    userId: 900008,
    bookId: 800009,
  },
  {
    loanId: 8451,
    userId: 900005,
    bookId: 800006,
  },
  {
    loanId: 8452,
    userId: 900002,
    bookId: 800009,
  },
  {
    loanId: 8453,
    userId: 900001,
    bookId: 800008,
  },
  {
    loanId: 8454,
    userId: 900006,
    bookId: 800004,
  },
  {
    loanId: 8455,
    userId: 900006,
    bookId: 800008,
  },
  {
    loanId: 8456,
    userId: 900003,
    bookId: 800005,
  },
  {
    loanId: 8457,
    userId: 900003,
    bookId: 800003,
  },
  {
    loanId: 8458,
    userId: 900006,
    bookId: 800009,
  },
  {
    loanId: 8459,
    userId: 900002,
    bookId: 800008,
  },
  {
    loanId: 8460,
    userId: 900010,
    bookId: 800008,
  },
  {
    loanId: 8461,
    userId: 900004,
    bookId: 800002,
  },
  {
    loanId: 8462,
    userId: 900002,
    bookId: 800001,
  },
  {
    loanId: 8463,
    userId: 900004,
    bookId: 800001,
  },
  {
    loanId: 8464,
    userId: 900007,
    bookId: 800001,
  },
  {
    loanId: 8465,
    userId: 900003,
    bookId: 800010,
  },
  {
    loanId: 8466,
    userId: 900005,
    bookId: 800002,
  },
  {
    loanId: 8467,
    userId: 900010,
    bookId: 800005,
  },
  {
    loanId: 8468,
    userId: 900010,
    bookId: 800001,
  },
  {
    loanId: 8469,
    userId: 900003,
    bookId: 800002,
  },
  {
    loanId: 8470,
    userId: 900005,
    bookId: 800005,
  },
  {
    loanId: 8471,
    userId: 900006,
    bookId: 800001,
  },
  {
    loanId: 8472,
    userId: 900009,
    bookId: 800001,
  },
  {
    loanId: 8473,
    userId: 900005,
    bookId: 800008,
  },
  {
    loanId: 8474,
    userId: 900008,
    bookId: 800010,
  },
  {
    loanId: 8475,
    userId: 900005,
    bookId: 800006,
  },
  {
    loanId: 8476,
    userId: 900005,
    bookId: 800002,
  },
  {
    loanId: 8477,
    userId: 900005,
    bookId: 800009,
  },
  {
    loanId: 8478,
    userId: 900006,
    bookId: 800005,
  },
  {
    loanId: 8479,
    userId: 900008,
    bookId: 800005,
  },
  {
    loanId: 8480,
    userId: 900008,
    bookId: 800009,
  },
  {
    loanId: 8481,
    userId: 900008,
    bookId: 800002,
  },
  {
    loanId: 8482,
    userId: 900010,
    bookId: 800007,
  },
  {
    loanId: 8483,
    userId: 900004,
    bookId: 800003,
  },
  {
    loanId: 8484,
    userId: 900008,
    bookId: 800002,
  },
  {
    loanId: 8485,
    userId: 900002,
    bookId: 800005,
  },
  {
    loanId: 8486,
    userId: 900002,
    bookId: 800003,
  },
  {
    loanId: 8487,
    userId: 900005,
    bookId: 800010,
  },
  {
    loanId: 8488,
    userId: 900008,
    bookId: 800003,
  },
  {
    loanId: 8489,
    userId: 900003,
    bookId: 800008,
  },
];

export const options = {
  vus: 20,
  duration: "30s",
};

export default function () {
  const loan = activeLoans[Math.floor(Math.random() * activeLoans.length)];

  const payload = {
    selectedLoans: JSON.stringify([
      {
        loanId: loan.loanId,
        userId: loan.userId,
        bookId: loan.bookId,
        status: "baik",
      },
    ]),
  };

  const res = http.post(
    "https://project-perpustakaan-sql-402234429231.us-central1.run.app/peminjaman-perpustakaan/return-multiple",
    payload,
  );

  console.log(`RETURN | Loan: ${loan.loanId} | Status: ${res.status}`);

  sleep(1);
}
