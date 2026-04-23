import "./App.css";

export default function Home() {
  return (
    <div className="page">
    {/*Top bar*/}
      <header className="topbar minimal">
      <h1>HabitFlow</h1>
      </header>

      
      <main className="wrap">
      <section className="hero">
        <div className="left">
          <div className="pill"> Calm habit tracking</div>

          <h2 className="title">Stay consistent without burnout.</h2>

          <p className="subtitle">
            Build habits that actually stick. Simple tracking, gentle reminders, and real progress.
            </p>
        
        <div className="ctaRow">
          <a className="btn" href="/habitlilli/register.php">
          Start free
        </a>
          <a className="btn ghost" href="/habitlilli/login.php">
          Sign in 
          </a>
          </div>


         <div className="trustRow">
          <span>✔ No pressure</span>
          <span>⚪</span>
          <span>✔ Private by design</span>
          <span>⚪</span>
          <span>✔ Built for real life</span>
         </div>

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

        <div className="right" id="preview">
          <div className="card">
            <div className="cardHeader">
              <strong>Daily Habits</strong>
              <span className="muted">Your dashboard view</span>
            </div>

            <div className="habitRow done rich">
                <div className="habitMain">
                  <div className="habitTop">
                    <div className="habitName">Drink water</div>

                    <div className="streak">Streak : 5</div>
                  </div>
                  
                  <div className="habitMeta">Daily 2 min</div>

                  <div className="progressLine">
                    <div className="progressFill full" />
                  </div>
              </div>

             <span className="badge">Done</span>
            </div>
            
            <div className="habitRow rich">
              <div className="habitMain">
                <div className="habitMain">
                  <div className="habitName">10-min walk</div>
                    <div className="habitMeta">4x/week 10 min</div></div>


                    <div className="progressLine">
                    <div className="progressFill partial" />
                  </div>
              </div>

               <span className="badge ghost">4:00 PM</span>
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
                <div className="statLabel">Reports</div>
              </div>
            </div>
            <div className="miniMessage">Keep your streak going!</div>

              <div className="miniHabit">
                <span> Drink water</span>
                <span className="muted">Done</span>
            </div>
            
            <div className="streakFooter"> 2 Day streak</div>
             
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