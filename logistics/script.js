function handleCredentialResponse(response) {
    try {
        const userData = jwt_decode(response.credential);
        console.log(userData)
        const userEmail = userData.email || "Unavailable";
        document.getElementById("email").value = userEmail;
        console.log("User email captured:", userEmail);
    } catch (error) {
        console.error("Failed to capture email:", error);
    }
}

function generateBookingID() {
    const bookingID = Math.floor(100000 + Math.random() * 900000);
    document.getElementById("bid").value = bookingID;
}

function showVehicleList() {
    const category = document.getElementById("vehicle-category").value;
    const shofcoDropdown = document.getElementById("shofco-vehicles");
    const hireDropdown = document.getElementById("hire-vehicles");
    shofcoDropdown.style.display = category === "shv" ? "block" : "none";
    hireDropdown.style.display = category === "hrv" ? "block" : "none";
}

function handleSubmit(event) {
    event.preventDefault(); // Prevent default form submission

    // Validation logic
    const bid = document.getElementById("bid").value.trim();
    const email = document.getElementById("email").value.trim();
    const ddate = document.getElementById("ddate").value;
    const rdate = document.getElementById("rdate").value;
    const passno = document.getElementById("passno").value.trim();
    const dest = document.getElementById("dest").value.trim();
    const dept = document.getElementById("dept").value.trim();
    const pop = document.getElementById("pop").value.trim();
    const tin = document.getElementById("tin").value;
    const tout = document.getElementById("tout").value;
    const vehicleCategory = document.getElementById("vehicle-category").value;
    const shvList = document.getElementById("shv-list").value;
    const hrvList = document.getElementById("hrv-list").value;

    // Check if required fields are filled
    if (!bid || !email || !ddate || !rdate || !passno || !dest || !dept || !pop || !tin || !tout || !vehicleCategory) {
        alert("All fields are required!");
        return; // Stop further execution
    }

    // Check if a specific vehicle is selected
    if (vehicleCategory === "shv" && shvList === "-") {
        alert("Please select a SHOFCO vehicle.");
        return;
    }
    if (vehicleCategory === "hrv" && hrvList === "-") {
        alert("Please select a Hire vehicle.");
        return;
    }

    // Form submission using fetch API
    const form = document.getElementById("submissionForm");
    const formData = new FormData(form);

    fetch(form.action, {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.result === "success") {
                alert("Submitted Successfully!");
                form.reset();
                generateBookingID(); // Regenerate booking ID
            } else {
                alert("Error, Did not Submit: " + data.error);
            }
        })
        .catch((error) => {
            alert("Error, Did not Submit: " + error.message);
        });
}


document.addEventListener("DOMContentLoaded", () => {
    generateBookingID();

    document.getElementById("submissionForm").addEventListener("submit", handleSubmit);
});
