
import { NavLink } from "react-router-dom";
import "./Navbar.css";

function Navbar() {
  return (
    <header className="navbar">

      {/* Logo / System Name */}
      <div className="navbar-brand">
        <span className="brand-icon">♻</span>
        <span>SmartBin</span>
      </div>

      {/* Navigation Links */}
      <nav className="nav-links">
        <NavLink to="/" end>Dashboard</NavLink>
        <NavLink to="/bins">Bin Management</NavLink>
        <NavLink to="/alerts">Alerts & Collection</NavLink>
        <NavLink to="/settings">System Settings</NavLink>
      </nav>

      {/* Account */}
      <NavLink to="/account" className="account-link">
        <i className="fa-regular fa-user"></i>Account
      </NavLink>
    </header>
  );
}

export default Navbar;