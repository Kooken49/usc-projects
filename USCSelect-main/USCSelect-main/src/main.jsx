import React from "react";
import ReactDOM from "react-dom/client";
import VoterRegistration from "./pages/VoterRegistration.jsx";
import AdminRegistration from "./pages/AdminRegistration.jsx";
import { BrowserRouter, Routes, Route, Link } from "react-router-dom";
import WelcomePg from "./pages/WelcomePage.jsx";
import AdminLogin from "./pages/AdminLogin.jsx";
import CandidateRegistration from "./pages/CandidateRegistration.jsx";
import CandidateAgreement from "./pages/CandidateAgreement.jsx";

ReactDOM.createRoot(document.getElementById("root")).render(
  <React.StrictMode>
    <BrowserRouter>
      <div>
        <Routes>
          <Route path="/" element={<WelcomePg />} />
          <Route path="/VoterRegistration" element={<VoterRegistration />} />
          <Route path="/AdminRegistration" element={<AdminRegistration />} />
          <Route path="/AdminLogin" element={<AdminLogin />} />
          <Route path="/CandidateRegistration" element={<CandidateRegistration />} />
          <Route path="/CandidateAgreement" element={<CandidateAgreement />} />
          {/* // ADD ADDITIONAL PATHS BELOW */}
        </Routes>
      </div>
    </BrowserRouter>
  </React.StrictMode>
);
