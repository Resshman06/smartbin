import { BrowserRouter, Routes, Route } from "react-router-dom";
import Navbar from "./components/Navbar";
import Dashboard from "./pages/Dashboard";

// Testing For Navigation
function BinManagement() { return <h1>Bin Management</h1>; }
function AlertsCollection() { return <h1>Alerts & Collection</h1>; }
function SystemSettings() { return <h1>System Settings</h1>; }
function Account() { return <h1>Account</h1>; }

function App() {
  return (
    <BrowserRouter>
      <Navbar />
      <main className="page-content">
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/bins" element={<BinManagement />} />
          <Route path="/alerts" element={<AlertsCollection />} />
          <Route path="/settings" element={<SystemSettings />} />
          <Route path="/account" element={<Account />} />
        </Routes>
      </main>
    </BrowserRouter>
  );
}

export default App;