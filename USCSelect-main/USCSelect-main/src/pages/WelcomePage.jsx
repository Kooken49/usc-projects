import { useState } from "react";
import { Link, Link as ReactRouterLink } from "react-router-dom";
import "../App.css";
import welcomeBG from "../assets/backgrounds/WelcomePageBG.png";
import uscLogo from "../assets/backgrounds/uscLogo.png";

function WelcomePg() {
  const [count, setCount] = useState(0);

  return (
    <>
      <div className="welcomePageMain">
        <div className="logoSpace">
          <center>
            <img src={uscLogo} />
            <h5>Are you ready to vote?</h5>
            <button>
              <Link
                as={ReactRouterLink}
                to="/VoterRegistration"
                style={{
                  textDecoration: "none",
                }}
                className="vote-link"
              >
                Vote Now!
              </Link>
            </button>
          </center>
        </div>
        <img src={welcomeBG} alt="Welcome Background" />
      </div>
    </>
  );
}

export default WelcomePg;
