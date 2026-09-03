import { Button } from "@chakra-ui/react";
import React from "react";
import signinwithGooglePopup from "../firebase/signinwithGooglePopup";
import { FaGoogle } from "react-icons/fa";

function GoogleButton() {
  return (
    <Button
      leftIcon={<FaGoogle />}
      colorScheme="green"
      variant="solid"
      onClick={signinwithGooglePopup}
    >
      Continue with Google
    </Button>
  );
}

export default GoogleButton;
