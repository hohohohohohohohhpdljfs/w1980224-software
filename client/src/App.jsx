import "./App.css";

export default function App() {
  return (
    <div className="page">
    {/*Top bar*/}
      <header className="topbar">
      <div className="brand">
      <div className="logo" />

      <div className="brandText">
      <h1>HabitFlow</h1>
      <p>Build better habits, gently.</p>
      </div>
      </div>
      
      <nav className="nav">
        <a href="#features">Features</a>

        <a href="/habithost/login.php">Sign in</a>

        <a className="btn small" href="/habithost/register.php">
        Start free
        </a>
        </nav>
        </header>

      
      <main className="wrap">
      <section className="hero">
        <div className="left">
          <div className="pill"> Calm habit tracking</div>

          <h2 className="title">Stay consistent without pressure.</h2>

          <p className="subtitle">
            Quick daily check ins, simple progress and a gentle experience that helps you keep going.
            </p>
        
        <div className="ctaRow">
          <a className="btn ghost" href="/habithost/register.php">
          Start free
        </a>
          <a className="btn ghost" href="/habithost/login.php">
          Sign in 
          </a>
          </div>
          <div className="heroDivider" />

       <div id="features" className="features">
         <div className="feature">
          <span className="icon">🎯</span>
        <div>
           <h3>Flexible goals</h3>
           <p>Daily / weekly habits that fit real life</p>
         </div>
      </div>

       <div className="feature">
        <span className="icon">📈</span>
       <div>
       <h3>Clear progress</h3>
       <p>Completion rate + simple visuals</p>
       </div>
      </div>

       <div className="feature">
        <span className="icon">🔔</span>
        <div>
          <h3>Gentle reminders</h3>
          <p>Optional nudges when you want them.</p>
        </div>
        </div>
        </div>
        </div>

        <div className="right">
          <div className="card">
            <div className="cardHeader">
              <strong>Today (preview)</strong>
              <span className="muted">Your dashboard view</span>
            </div>
            <div className="habitRow done">
             <div>
              <div className="habitName">Drink water</div>
              <div className="habitMeta">Daily 2 min</div>
             </div>
             <span className="badge">Done</span>
            </div>

            <div className="habitRow">
              <div>
                <div className="habitName">10-min walk</div>
                <div className="habitMeta">4x/week 10 min</div>
              </div>
              <span className="badge ghost">Not yet</span>
            </div>
            
            <div className="habitRow">
              <div>
                <div className="habitName">Read 5 pages</div>
                <div className="habitMeta">Daily 5 min</div>
              </div>
              <span className="badge ghost">Not yet</span>
            </div>

            <div className="divider" />
            
            <div className="stats">
              <div className="stat">
                <div className="statNum">4</div>
                <div className="statLabel">Habits today</div>
              </div>
              <div className="stat">
                <div className="statNum">62%</div>
                <div className="statLabel">Weekly rate</div>
              </div>
              <div className="stat">
                <div className="statNum">+1</div>
                <div className="statLabel">Small win</div>
              </div>
            </div>
            
            <div className="footer">
              Already have an account?{" "}
              <a href="/habithost/login.php">Sign in</a>
            </div>
          </div>
        </div>
        </section>

        <footer className="siteFooter">
          <span> {new Date().getFullYear()} HabitFlow</span>
          <span className="muted">Frontend: React Backend: PHP + mySQL</span>     
        </footer>
        </main>
        </div>
  );
}

  
